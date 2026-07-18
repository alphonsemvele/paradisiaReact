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
        );

        if (! $resultat['ok']) {
            $payment->update([
                'status' => 'Failed',
                'error_code' => $resultat['code'],
            ]);

            return response()->json([
                'ok' => false,
                'code' => $resultat['code'],
                'message' => $resultat['message'],
                'representants' => $resultat['representants'] ?? [],
            ], 422);
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
}
