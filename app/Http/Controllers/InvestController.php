<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Round;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class InvestController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('dashboard/invest/index', [
            'stats' => $this->getGlobalStats(),
            'currentRound' => $this->getCurrentRound(),
            'topInvestors' => $this->getTopInvestors(),
            'userInvestment' => $this->getUserInvestment(),
            'paymentHistory' => $this->getPaymentHistory(),
            'monthlyStats' => $this->getMonthlyStats(),
        ]);
    }

    public function quickInvest(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return back()->with('info', 'Le service d\'investissement Malpay sera bientôt disponible.');
    }

    private function getGlobalStats(): array
    {
        $totalInvested = Payment::where('payments.status', 'Success')
            ->whereNotNull('id_round')
            ->join('rounds', 'payments.id_round', '=', 'rounds.id')
            ->sum(DB::raw('payments.share * rounds.amount'));

        $totalShares = Payment::where('status', 'Success')
            ->whereNotNull('id_round')
            ->sum('share');

        $totalInvestors = Payment::where('status', 'Success')
            ->whereNotNull('id_round')
            ->distinct('id_user')
            ->count('id_user');

        $countriesCount = Payment::where('status', 'Success')
            ->whereNotNull('id_round')
            ->whereNotNull('payment_country')
            ->distinct('payment_country')
            ->count('payment_country');

        return [
            'total_invested' => (float) $totalInvested,
            'total_shares' => (int) $totalShares,
            'total_investors' => (int) $totalInvestors,
            'countries_count' => (int) $countriesCount,
        ];
    }

    private function getCurrentRound(): ?array
    {
        $round = Round::where('status', 'Success')
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $round) {
            return null;
        }

        return [
            'id' => $round->id,
            'name' => $round->name,
            'amount' => (float) $round->amount,
            'begin' => $round->begin?->format('d/m/Y'),
            'end' => $round->end?->format('d/m/Y'),
            'days_remaining' => $round->end ? now()->diffInDays($round->end, false) : null,
        ];
    }

    private function getTopInvestors()
    {
        return Payment::where('payments.status', 'Success')
            ->whereNotNull('payments.id_round')
            ->join('rounds', 'payments.id_round', '=', 'rounds.id')
            ->join('users', 'payments.id_user', '=', 'users.id')
            ->select(
                'users.id',
                'users.ref',
                'users.name',
                'users.last_name',
                'users.photo',
                DB::raw('SUM(payments.share * rounds.amount) as total_invested'),
                DB::raw('SUM(payments.share) as total_shares')
            )
            ->groupBy('users.id', 'users.ref', 'users.name', 'users.last_name', 'users.photo')
            ->orderByDesc('total_invested')
            ->limit(5)
            ->get()
            ->map(fn ($i) => [
                'id' => $i->id,
                'ref' => $i->ref,
                'name' => $i->name,
                'photo' => $i->photo ? Storage::url($i->photo) : null,
                'total_invested' => (float) $i->total_invested,
                'total_shares' => (int) $i->total_shares,
            ]);
    }

    private function getUserInvestment(): array
    {
        if (! Auth::check()) {
            return ['total' => 0, 'shares' => 0];
        }

        $data = Payment::where('payments.status', 'Success')
            ->whereNotNull('payments.id_round')
            ->where('payments.id_user', Auth::id())
            ->join('rounds', 'payments.id_round', '=', 'rounds.id')
            ->select(
                DB::raw('SUM(payments.share * rounds.amount) as total_invested'),
                DB::raw('SUM(payments.share) as total_shares')
            )
            ->first();

        return [
            'total' => (float) ($data->total_invested ?? 0),
            'shares' => (int) ($data->total_shares ?? 0),
        ];
    }

    private function getPaymentHistory()
    {
        return Payment::where('payments.status', 'Success')
            ->whereNotNull('payments.id_round')
            ->join('rounds', 'payments.id_round', '=', 'rounds.id')
            ->join('users', 'payments.id_user', '=', 'users.id')
            ->select(
                'payments.id',
                'payments.created_at',
                'payments.share',
                'rounds.name as round_name',
                'rounds.amount as round_amount',
                'users.name as user_name',
                'users.ref as user_ref',
                'users.photo as user_photo',
                DB::raw('payments.share * rounds.amount as invested_amount')
            )
            ->orderByDesc('payments.created_at')
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'created_at' => $p->created_at,
                'created_at_formatted' => $p->created_at->format('d/m/Y H:i'),
                'created_at_human' => $p->created_at->diffForHumans(),
                'share' => (int) $p->share,
                'round_name' => $p->round_name,
                'invested_amount' => (float) $p->invested_amount,
                'user' => [
                    'name' => $p->user_name,
                    'ref' => $p->user_ref,
                    'photo' => $p->user_photo ? Storage::url($p->user_photo) : null,
                ],
            ]);
    }

    private function getMonthlyStats(): array
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        $currentMonthTotal = Payment::where('payments.status', 'Success')
            ->whereNotNull('id_round')
            ->where('payments.created_at', '>=', $currentMonth)
            ->join('rounds', 'payments.id_round', '=', 'rounds.id')
            ->sum(DB::raw('payments.share * rounds.amount'));

        $lastMonthTotal = Payment::where('payments.status', 'Success')
            ->whereNotNull('id_round')
            ->whereBetween('payments.created_at', [$lastMonth, $currentMonth])
            ->join('rounds', 'payments.id_round', '=', 'rounds.id')
            ->sum(DB::raw('payments.share * rounds.amount'));

        $growth = $lastMonthTotal > 0
            ? (($currentMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100
            : 0;

        return [
            'current' => (float) $currentMonthTotal,
            'last' => (float) $lastMonthTotal,
            'growth' => round($growth, 1),
        ];
    }
}