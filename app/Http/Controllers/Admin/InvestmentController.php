<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Round;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/** Administration des investissements : stats, achats de parts, crédit manuel. */
class InvestmentController extends Controller
{
    public function index(): Response
    {
        // Un investissement = un paiement Success rattaché à un round.
        $totalShares = (float) Payment::where('status', 'Success')->whereNotNull('id_round')->sum('share');
        $totalInvestors = (int) Payment::where('status', 'Success')->whereNotNull('id_round')
            ->distinct('id_user')->count('id_user');

        // Avec jointure rounds : on QUALIFIE « status » (présent des deux côtés).
        $totalInvested = (float) Payment::where('payments.status', 'Success')
            ->whereNotNull('payments.id_round')
            ->join('rounds', 'payments.id_round', '=', 'rounds.id')
            ->sum(DB::raw('payments.share * rounds.amount'));

        // Rounds (tous, actifs ou non) avec parts vendues.
        $rounds = Round::orderByDesc('id')->get()->map(function (Round $r) {
            $parts = (float) Payment::where('status', 'Success')->where('id_round', $r->id)->sum('share');

            return [
                'id' => $r->id,
                'name' => $r->name,
                'prix_part' => (float) $r->amount,
                'status' => $r->status,
                'actif' => $r->status === 'Success',
                'begin' => $r->begin?->format('d/m/Y'),
                'end' => $r->end?->format('d/m/Y'),
                'parts_vendues' => $parts,
                'collecte' => $parts * (float) $r->amount,
            ];
        });

        // Derniers achats de parts.
        $derniers = DB::table('payments')
            ->join('users', 'users.id', '=', 'payments.id_user')
            ->join('rounds', 'rounds.id', '=', 'payments.id_round')
            ->whereNotNull('payments.id_round')
            ->orderByDesc('payments.id')
            ->limit(40)
            ->get([
                'payments.id', 'payments.share', 'payments.amount', 'payments.status',
                'payments.type_paiement', 'payments.created_at',
                'users.id as user_id', 'users.name as user_name',
                'rounds.name as round_name', 'rounds.amount as prix_part',
            ])
            ->map(fn ($p) => [
                'id' => $p->id,
                'user_id' => $p->user_id,
                'user' => $p->user_name,
                'round' => $p->round_name,
                'parts' => (float) $p->share,
                'montant' => (float) ($p->amount ?: $p->share * $p->prix_part),
                'status' => $p->status,
                'type' => $p->type_paiement,
                'credit' => $p->type_paiement === 'Bonus',
                'date' => $p->created_at ? \Illuminate\Support\Carbon::parse($p->created_at)->isoFormat('D MMM YYYY [à] HH:mm') : null,
            ]);

        return Inertia::render('admin/investissements/index', [
            'stats' => [
                'total_invested' => $totalInvested,
                'total_shares' => $totalShares,
                'total_investors' => $totalInvestors,
                'nb_rounds' => Round::count(),
            ],
            'rounds' => $rounds,
            'derniers' => $derniers,
        ]);
    }

    /** Crédite un nombre de parts à un utilisateur, sur un round (même inactif). */
    public function crediter(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_user' => ['required', 'integer', 'exists:users,id'],
            'id_round' => ['required', 'integer', 'exists:rounds,id'],
            'parts' => ['required', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $round = Round::findOrFail($data['id_round']);
        $montant = (float) $data['parts'] * (float) $round->amount;

        Payment::create([
            'ref' => 'CRED-'.strtoupper(Str::random(8)),
            'id_round' => $round->id,
            'id_project' => $round->id_project,
            'id_user' => $data['id_user'],
            'id_agent' => auth()->id(),
            'amount' => $montant,
            'total_amount' => $montant,
            'fees' => 0,
            'currency' => 'FCFA',
            'services' => 'invest',
            'share' => $data['parts'],
            'status' => 'Success',
            'type_paiement' => 'Bonus', // crédit manuel administration
            'description_payment' => $data['note'] ?? 'Crédit manuel de parts par l\'administration',
        ]);

        $u = User::find($data['id_user']);

        return back()->with('success', "{$data['parts']} part(s) créditée(s) à {$u->name} sur « {$round->name} ».");
    }

    /** Recherche d'utilisateurs pour le crédit. */
    public function rechercheUsers(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('q', ''));

        $users = User::query()
            ->when($q !== '', fn ($x) => $x->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")))
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'email'])
            ->map(fn ($u) => ['id' => $u->id, 'name' => trim($u->name.' '.($u->last_name ?? '')), 'email' => $u->email]);

        return response()->json(['users' => $users]);
    }
}
