<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Round;
use App\Services\MalaPay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $payment->update(['status' => 'Success']);

        return response()->json([
            'ok' => true,
            'reference' => $reference,
            'parts' => $validated['parts'],
            'montant_formate' => number_format($montant, 0, ',', ' ').' '.$devise,
            'solde_restant' => $resultat['data']['solde_restant_formate'] ?? null,
            'message' => sprintf(
                'Investissement confirmé : %d part%s pour %s.',
                $validated['parts'],
                $validated['parts'] > 1 ? 's' : '',
                number_format($montant, 0, ',', ' ').' '.$devise
            ),
        ]);
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
