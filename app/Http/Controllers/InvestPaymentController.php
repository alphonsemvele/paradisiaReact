<?php

namespace App\Http\Controllers;

use App\Mail\InvestissementConfirmeMail;
use App\Models\Payment;
use App\Models\Round;
use App\Services\MalaPay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Paiement d'un investissement via les portefeuilles Malapay.
 *
 * Parcours : l'investisseur choisit son pays (la devise en découle), saisit le
 * code de son portefeuille Malapay, le système vérifie devise et solde, puis
 * débite. Toute anomalie renvoie les représentants habilités à créditer le
 * portefeuille.
 *
 * Le montant n'est jamais pris depuis le navigateur : il est recalculé à
 * partir du round en cours et du nombre de parts.
 */
class InvestPaymentController extends Controller
{
    /** Un investisseur ne peut pas acheter plus de parts d'un coup. */
    private const PARTS_MAX = 1000;

    public function __construct(private readonly MalaPay $malapay) {}

    /** Pays et devises proposés au paiement. */
    public function pays(): JsonResponse
    {
        return response()->json([
            'disponible' => $this->malapay->estConfigure(),
            'pays' => $this->malapay->pays(),
            // Où créer un portefeuille quand l'investisseur n'en a pas
            'url_portefeuilles' => rtrim((string) config('services.malapay.site'), '/').'/portefeuilles',
        ]);
    }

    /**
     * Moyens de paiement disponibles pour un pays : toujours le portefeuille
     * MalaPay, plus les opérateurs mobile money là où ils sont proposés
     * (Cameroun : Orange Money, MTN Mobile Money).
     */
    public function operateurs(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pays' => ['required', 'string', 'size:2'],
        ]);

        $donnees = $this->malapay->operateurs(strtoupper($validated['pays']));

        return response()->json([
            'ok' => true,
            'operateurs' => $donnees['operateurs'],
            'mode_integration' => $donnees['mode_integration'],
        ]);
    }

    /**
     * Vérifie le portefeuille avant paiement : devise cohérente avec le pays
     * choisi, solde suffisant pour le nombre de parts demandé.
     */
    public function verifier(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['ok' => false, 'message' => 'Connectez-vous pour investir.'], 401);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'min:10', 'max:128'],
            'pays' => ['required', 'string', 'size:2'],
            'devise' => ['required', 'string', 'size:3'],
            'parts' => ['required', 'integer', 'min:1', 'max:'.self::PARTS_MAX],
        ]);

        $round = $this->roundActif();

        if (! $round) {
            return response()->json(['ok' => false, 'message' => 'Aucun round d\'investissement n\'est ouvert.'], 422);
        }

        $montant = $this->montant($round, $validated['parts']);

        $resultat = $this->malapay->verifierPortefeuille(
            code: $validated['code'],
            devise: strtoupper($validated['devise']),
            montant: $montant,
            pays: strtoupper($validated['pays']),
        );

        if (! $resultat['ok']) {
            return response()->json([
                'ok' => false,
                'code' => $resultat['code'],
                'message' => $resultat['message'],
                'representants' => $resultat['representants'] ?? [],
            ], 422);
        }

        $portefeuille = $resultat['data'];

        // Malapay répond « success » même quand le portefeuille n'est pas
        // payable : c'est le drapeau payable, avec ses alertes, qui tranche.
        if (! ($portefeuille['payable'] ?? false)) {
            $alerte = $portefeuille['alertes'][0] ?? [];

            return response()->json([
                'ok' => false,
                'code' => $alerte['code'] ?? 'WALLET_NOT_PAYABLE',
                'message' => $alerte['message'] ?? 'Ce portefeuille ne permet pas ce paiement.',
                'representants' => $portefeuille['representants'] ?? [],
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'montant' => $montant,
            'montant_formate' => number_format($montant, 0, ',', ' ').' '.strtoupper($validated['devise']),
            'portefeuille' => $portefeuille,
        ]);
    }

    /**
     * Débite le portefeuille et enregistre l'investissement.
     *
     * La référence est générée côté serveur puis réutilisée par Malapay comme
     * clé d'idempotence : une double soumission ne débite qu'une fois.
     */
    public function payer(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['ok' => false, 'message' => 'Connectez-vous pour investir.'], 401);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'min:10', 'max:128'],
            'pays' => ['required', 'string', 'size:2'],
            'devise' => ['required', 'string', 'size:3'],
            'parts' => ['required', 'integer', 'min:1', 'max:'.self::PARTS_MAX],
        ]);

        $round = $this->roundActif();

        if (! $round) {
            return response()->json(['ok' => false, 'message' => 'Aucun round d\'investissement n\'est ouvert.'], 422);
        }

        $devise = strtoupper($validated['devise']);
        $montant = $this->montant($round, $validated['parts']);
        $reference = 'INV_'.strtoupper(Str::random(12));
        $user = Auth::user();

        // Enregistré « pending » AVANT l'appel : si Malapay répond mal ou que
        // la connexion tombe, la trace du paiement tenté existe.
        $payment = Payment::create([
            'ref' => $reference,
            'id_round' => $round->id,
            'id_user' => $user->id,
            'amount' => $montant,
            'total_amount' => $montant,
            'currency' => $devise,
            'share' => $validated['parts'],
            'status' => 'pending',
            'type_paiement' => 'Wallet',
            'payment_country' => strtoupper($validated['pays']),
            'customer_email' => $user->email,
            'customer_name' => $user->name,
        ]);

        $resultat = $this->malapay->debiter(
            code: $validated['code'],
            montant: $montant,
            devise: $devise,
            reference: $reference,
            description: sprintf('Investissement Paradisia — %d part%s (%s)',
                $validated['parts'],
                $validated['parts'] > 1 ? 's' : '',
                $round->name
            ),
            service: 'investissement',
        );

        if (! $resultat['ok']) {
            $this->conclureEchec($payment, $resultat['code']);

            return response()->json([
                'ok' => false,
                'code' => $resultat['code'],
                'message' => $resultat['message'],
                'representants' => $resultat['representants'] ?? [],
            ], $this->statutHttp($resultat['code']));
        }

        // Malapay n'exécute plus le débit immédiatement : le titulaire du
        // portefeuille doit d'abord valider par le lien reçu par e-mail.
        // Le paiement reste donc « pending » jusqu'à confirmation.
        $donnees = $resultat['data'] ?? [];

        if (($donnees['validation_requise'] ?? false) || ($donnees['statut'] ?? null) === 'en_attente') {
            return response()->json([
                'ok' => true,
                'en_attente' => true,
                'reference' => $reference,
                'parts' => $validated['parts'],
                'montant_formate' => number_format($montant, 0, ',', ' ').' '.$devise,
                'email_masque' => $donnees['email_masque'] ?? null,
                'expire_at' => $donnees['expire_at'] ?? null,
                'message' => 'Un e-mail de validation vient d\'être envoyé au titulaire du portefeuille. '
                    .'Le paiement sera confirmé dès que le lien aura été ouvert.',
            ], 202);
        }

        $payment->update(['status' => 'Success']);
        $this->notifierInvestisseur($payment);

        return response()->json([
            'ok' => true,
            'en_attente' => false,
            'reference' => $reference,
            'parts' => $validated['parts'],
            'montant_formate' => number_format($montant, 0, ',', ' ').' '.$devise,
            'solde_restant' => $donnees['solde_restant_formate'] ?? null,
            'message' => sprintf(
                'Investissement confirmé : %d part%s pour %s.',
                $validated['parts'],
                $validated['parts'] > 1 ? 's' : '',
                number_format($montant, 0, ',', ' ').' '.$devise
            ),
        ]);
    }

    /**
     * Paie l'investissement par mobile money (Orange/MTN). MalaPay renvoie une
     * URL de paiement vers laquelle rediriger l'investisseur ; le paiement est
     * confirmé ensuite (notification MalaPay), suivi par polling de statut().
     */
    /**
     * GET /invest/paiement/commission — total exact pour un nombre de parts.
     *
     * Appelé par le formulaire avant validation : l'investisseur voit ce qu'il
     * va réellement régler, frais compris le cas échéant.
     */
    public function commission(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['ok' => false, 'message' => 'Connectez-vous pour investir.'], 401);
        }

        $validated = $request->validate([
            'parts' => ['required', 'integer', 'min:1'],
        ]);

        $round = $this->roundOuvert();

        if (! $round) {
            return response()->json(['ok' => false, 'message' => 'Aucun round d\'investissement n\'est ouvert.'], 422);
        }

        $montant = round($validated['parts'] * (float) $round->share_price, 2);
        $resultat = $this->malapay->commission($montant);

        // Malapay indisponible : on affiche le prix sec plutôt que de bloquer
        // le formulaire. Le total exact sera de toute façon confirmé à l'étape
        // de paiement.
        if (! $resultat['ok']) {
            return response()->json([
                'ok' => true,
                'montant' => $montant,
                'montant_a_payer' => $montant,
                'commission' => 0,
                'mention' => null,
            ]);
        }

        $d = $resultat['data'] ?? [];

        return response()->json([
            'ok' => true,
            'montant' => (float) ($d['montant'] ?? $montant),
            'montant_a_payer' => (float) ($d['montant_a_payer'] ?? $montant),
            'commission' => (float) ($d['commission'] ?? 0),
            'commission_a_charge' => $d['commission_a_charge'] ?? 'projet',
            'mention' => $d['mention'] ?? null,
        ]);
    }

    public function payerMobile(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['ok' => false, 'message' => 'Connectez-vous pour investir.'], 401);
        }

        $validated = $request->validate([
            'pays' => ['required', 'string', 'size:2'],
            'devise' => ['required', 'string', 'size:3'],
            'parts' => ['required', 'integer', 'min:1', 'max:'.self::PARTS_MAX],
            'operateur' => ['required', 'string', 'in:orange,mtn'],
            'telephone' => ['required', 'string', 'max:20'],
        ]);

        $round = $this->roundActif();

        if (! $round) {
            return response()->json(['ok' => false, 'message' => 'Aucun round d\'investissement n\'est ouvert.'], 422);
        }

        $devise = strtoupper($validated['devise']);
        $montant = $this->montant($round, $validated['parts']);
        $reference = 'INV_'.strtoupper(Str::random(12));
        $user = Auth::user();

        $payment = Payment::create([
            'ref' => $reference,
            'id_round' => $round->id,
            'id_user' => $user->id,
            'amount' => $montant,
            'total_amount' => $montant,
            'currency' => $devise,
            'share' => $validated['parts'],
            'status' => 'pending',
            'type_paiement' => 'Mobile',
            'payment_country' => strtoupper($validated['pays']),
            'customer_email' => $user->email,
            'customer_name' => $user->name,
        ]);

        $resultat = $this->malapay->payerMobile(
            reference: $reference,
            montant: $montant,
            devise: $devise,
            pays: strtoupper($validated['pays']),
            operateur: $validated['operateur'],
            telephone: $validated['telephone'],
            description: sprintf('Investissement Paradisia — %d part%s (%s)',
                $validated['parts'],
                $validated['parts'] > 1 ? 's' : '',
                $round->name
            ),
            service: 'investissement',
            urlRetour: url('/invest?ref='.$reference),
            clientNom: $user->name,
            clientEmail: $user->email,
        );

        if (! $resultat['ok']) {
            $this->conclureEchec($payment, $resultat['code']);

            return response()->json([
                'ok' => false,
                'code' => $resultat['code'],
                'message' => $resultat['message'],
            ], $this->statutHttp($resultat['code']));
        }

        $donnees = $resultat['data'] ?? [];

        // Malapay renvoie trois montants distincts : le prix des parts, ce que
        // l'investisseur règle (commission comprise si elle est à sa charge) et
        // ce qui nous revient. On enregistre les deux qui nous concernent,
        // sinon la comptabilité ne retomberait pas sur le relevé.
        $aPayer = (float) ($donnees['montant_a_payer'] ?? $montant);
        $recu = (float) ($donnees['montant_recu'] ?? $montant);

        $payment->update([
            'total_amount' => $aPayer,
            'amount' => $recu,
        ]);

        return response()->json([
            'ok' => true,
            'en_attente' => true,
            'reference' => $reference,
            'parts' => $validated['parts'],
            'montant_formate' => number_format($aPayer, 0, ',', ' ').' '.$devise,
            'montant_a_payer' => $aPayer,
            'commission' => (float) ($donnees['commission'] ?? 0),
            'commission_a_charge' => $donnees['commission_a_charge'] ?? 'projet',
            'url_paiement' => $donnees['url_paiement'] ?? null,
            // Recours si l'invite ne s'affiche pas seule sur le téléphone.
            'code_ussd' => $donnees['code_ussd'] ?? null,
            'operateur_libelle' => $donnees['operateur_libelle'] ?? null,
            'message' => $donnees['instruction'] ?? 'Finalisez votre paiement mobile money pour valider votre investissement.',
        ], 202);
    }

    /**
     * Suivi d'un paiement en attente : le navigateur interroge cette route
     * pendant que le titulaire valide depuis sa boîte e-mail.
     */
    public function statut(string $reference): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['ok' => false, 'message' => 'Connectez-vous pour investir.'], 401);
        }

        // On ne consulte que ses propres paiements.
        $payment = Payment::where('ref', $reference)
            ->where('id_user', Auth::id())
            ->first();

        if (! $payment) {
            return response()->json(['ok' => false, 'message' => 'Paiement introuvable.'], 404);
        }

        if ($payment->status !== 'pending') {
            return response()->json([
                'ok' => true,
                'statut' => $payment->status,
                'termine' => true,
                'reussi' => $payment->status === 'Success',
            ]);
        }

        // Paiement mobile money : on interroge le suivi mobile de MalaPay.
        if ($payment->type_paiement === 'Mobile') {
            return $this->statutMobile($payment, $reference);
        }

        $resultat = $this->malapay->statutDebit($reference);

        if (! $resultat['ok']) {
            return response()->json(['ok' => true, 'statut' => 'pending', 'termine' => false]);
        }

        $statutMalapay = $resultat['data']['statut'] ?? 'en_attente';

        // On aligne le paiement Paradisia sur l'état constaté chez Malapay.
        if ($statutMalapay === 'complete') {
            $payment->update(['status' => 'Success']);
            $this->notifierInvestisseur($payment);

            return response()->json([
                'ok' => true,
                'statut' => 'Success',
                'termine' => true,
                'reussi' => true,
                'solde_restant' => $resultat['data']['solde_restant_formate'] ?? null,
                'message' => 'Paiement validé : votre investissement est enregistré.',
            ]);
        }

        if (in_array($statutMalapay, ['expire', 'annule'], true)) {
            $payment->update(['status' => 'Failed', 'error_code' => strtoupper($statutMalapay)]);

            return response()->json([
                'ok' => true,
                'statut' => 'Failed',
                'termine' => true,
                'reussi' => false,
                'message' => $statutMalapay === 'expire'
                    ? 'Le lien de validation a expiré. Relancez le paiement pour en recevoir un nouveau.'
                    : 'Le paiement a été annulé.',
            ]);
        }

        return response()->json(['ok' => true, 'statut' => 'pending', 'termine' => false]);
    }

    /**
     * Rapproche un paiement mobile money de l'état constaté chez MalaPay.
     */
    /**
     * Conclut un paiement en attente à partir de l'état chez Malapay, hors de
     * tout contexte de requête du client.
     *
     * Appelé au chargement de la page d'investissement : c'est ce qui garantit
     * qu'un achat valide sur le téléphone finit dans l'historique, même si
     * l'investisseur a fermé l'onglet aussitôt après.
     */
    public function conclureSiTermine(Payment $payment): void
    {
        $resultat = $this->malapay->statutMobile($payment->ref);

        if (! $resultat['ok']) {
            return;
        }

        $statut = $resultat['data']['statut'] ?? 'en_attente';

        if ($statut === 'reussi') {
            $payment->update(['status' => 'Success']);
            $this->notifierInvestisseur($payment);

            return;
        }

        if (in_array($statut, ['echoue', 'annule', 'expire'], true)) {
            $payment->update(['status' => 'Failed', 'error_code' => strtoupper($statut)]);
        }
    }

    private function statutMobile(Payment $payment, string $reference): JsonResponse
    {
        $resultat = $this->malapay->statutMobile($reference);

        if (! $resultat['ok']) {
            return response()->json(['ok' => true, 'statut' => 'pending', 'termine' => false]);
        }

        $statut = $resultat['data']['statut'] ?? 'en_attente';

        if ($statut === 'reussi') {
            $payment->update(['status' => 'Success']);
            $this->notifierInvestisseur($payment);

            return response()->json([
                'ok' => true,
                'statut' => 'Success',
                'termine' => true,
                'reussi' => true,
                'message' => 'Paiement confirmé : votre investissement est enregistré.',
            ]);
        }

        if (in_array($statut, ['echoue', 'annule', 'expire'], true)) {
            $payment->update(['status' => 'Failed', 'error_code' => strtoupper($statut)]);

            return response()->json([
                'ok' => true,
                'statut' => 'Failed',
                'termine' => true,
                'reussi' => false,
                'message' => $statut === 'annule'
                    ? 'Le paiement a été annulé.'
                    : 'Le paiement mobile money n\'a pas abouti. Vous pouvez réessayer.',
            ]);
        }

        return response()->json(['ok' => true, 'statut' => 'pending', 'termine' => false]);
    }

    /**
     * Confirme l'investissement à l'investisseur, côté Paradisia. Un échec
     * d'envoi ne remet jamais en cause le paiement déjà encaissé.
     */
    private function notifierInvestisseur(Payment $payment): void
    {
        if (! $payment->customer_email) {
            return;
        }

        try {
            Mail::to($payment->customer_email)->send(new InvestissementConfirmeMail($payment));
        } catch (\Throwable $e) {
            Log::error("Confirmation d'investissement {$payment->ref} non envoyée : ".$e->getMessage());
        }
    }

    /* ═══════════════════════ Interne ═══════════════════════ */

    private function roundActif(): ?Round
    {
        return Round::where('status', 'Success')
            ->orderByDesc('created_at')
            ->first();
    }

    /** Montant recalculé côté serveur : le navigateur ne fixe pas le prix. */
    private function montant(Round $round, int $parts): float
    {
        return round((float) $round->amount * $parts, 2);
    }

    /**
     * Causes où Malapay n'a rien enregistré : l'appel n'a pas abouti, aucun
     * paiement n'existe de leur côté.
     *
     * @var list<string>
     */
    private const ECHECS_SANS_TENTATIVE = [
        'MALAPAY_UNREACHABLE',
        'MALAPAY_RATE_LIMITED',
        'MALAPAY_NOT_CONFIGURED',
    ];

    /**
     * Clôt un paiement qui n'a pas abouti.
     *
     * Un quota dépassé ou un service injoignable ne sont pas des échecs de
     * paiement : rien n'a été tenté chez Malapay. Les marquer « Failed »
     * remplirait l'historique de l'investisseur de refus qui n'ont jamais eu
     * lieu. La référence étant régénérée à chaque tentative, supprimer
     * l'ébauche ne bloque aucune reprise.
     */
    private function conclureEchec(Payment $payment, string $code): void
    {
        if (in_array($code, self::ECHECS_SANS_TENTATIVE, true)) {
            $payment->delete();

            return;
        }

        $payment->update(['status' => 'Failed', 'error_code' => $code]);
    }

    /**
     * 503 pour une indisponibilité passagère, 429 pour un quota dépassé : le
     * client sait ainsi qu'il peut réessayer, là où un 422 signale un refus
     * définitif sur lequel il est inutile d'insister.
     */
    private function statutHttp(string $code): int
    {
        return match ($code) {
            'MALAPAY_RATE_LIMITED' => 429,
            'MALAPAY_UNREACHABLE', 'MALAPAY_NOT_CONFIGURED', 'PROJECT_INACTIVE' => 503,
            default => 422,
        };
    }
}
