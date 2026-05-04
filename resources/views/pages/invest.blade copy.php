<?php

use function Laravel\Folio\{name};
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Payment;
use App\Models\Round;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

name('invest');

new class extends Component {
    use WithPagination;

    public $investAmount = 100000;
    public $investDuration = 3;
    public $quickInvestAmount = '';
    public $quickSharesCount = 1;

    public function getTotalInvestedProperty()
    {
        return Payment::where('payments.status', 'Success')
            ->whereNotNull('id_round')
            ->join('rounds', 'payments.id_round', '=', 'rounds.id')
            ->sum(DB::raw('payments.share * rounds.amount'));
    }

    public function getTotalSharesProperty()
    {
        return Payment::where('status', 'Success')
            ->whereNotNull('id_round')
            ->sum('share');
    }

    public function getTotalInvestorsProperty()
    {
        return Payment::where('status', 'Success')
            ->whereNotNull('id_round')
            ->distinct('id_user')
            ->count('id_user');
    }

    public function getCountriesCountProperty()
    {
        return Payment::where('status', 'Success')
            ->whereNotNull('id_round')
            ->whereNotNull('payment_country')
            ->distinct('payment_country')
            ->count('payment_country');
    }

    public function getCurrentRoundProperty()
    {
        return Round::where('status', 'Success')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getTopInvestorsProperty()
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
            ->get();
    }

    public function getUserInvestmentProperty()
    {
        if (!Auth::check()) {
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
            'total' => $data->total_invested ?? 0,
            'shares' => $data->total_shares ?? 0
        ];
    }

    public function getPaymentHistoryProperty()
    {
        return Payment::where('payments.status', 'Success')
            ->whereNotNull('payments.id_round')
            ->join('rounds', 'payments.id_round', '=', 'rounds.id')
            ->join('users', 'payments.id_user', '=', 'users.id')
            ->select(
                'payments.*',
                'rounds.name as round_name',
                'rounds.amount as round_amount',
                'users.name as user_name',
                'users.ref as user_ref',
                DB::raw('payments.share * rounds.amount as invested_amount')
            )
            ->orderByDesc('payments.created_at')
            ->limit(10)
            ->get();
    }

    public function getMonthlyStatsProperty()
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
            'current' => $currentMonthTotal,
            'last' => $lastMonthTotal,
            'growth' => round($growth, 1)
        ];
    }

    public function with(): array
    {
        return [
            'totalInvested' => $this->getTotalInvestedProperty(),
            'totalShares' => $this->getTotalSharesProperty(),
            'totalInvestors' => $this->getTotalInvestorsProperty(),
            'countriesCount' => $this->getCountriesCountProperty(),
            'currentRound' => $this->getCurrentRoundProperty(),
            'topInvestors' => $this->getTopInvestorsProperty(),
            'userInvestment' => $this->getUserInvestmentProperty(),
            'paymentHistory' => $this->getPaymentHistoryProperty(),
            'monthlyStats' => $this->getMonthlyStatsProperty(),
        ];
    }
}; ?>

<x-layouts.app>
    @volt
    <div>
        <div class="w-full bg-green-50 min-h-screen relative overflow-hidden">
            <!-- Animated Floating Fruits Background -->
            <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
                <div class="fruit-float fruit-1">🍊</div>
                <div class="fruit-float fruit-2">🍋</div>
                <div class="fruit-float fruit-3">🥭</div>
                <div class="fruit-float fruit-4">🍍</div>
                <div class="fruit-float fruit-5">🍇</div>
                <div class="fruit-float fruit-6">🍎</div>
                <div class="fruit-float fruit-7">🍑</div>
                <div class="fruit-float fruit-8">🥝</div>
                <div class="fruit-float fruit-9">🍓</div>
                <div class="fruit-float fruit-10">🫐</div>
                <div class="fruit-float fruit-11">🍈</div>
                <div class="fruit-float fruit-12">🍒</div>
                <div class="leaf leaf-1">🌿</div>
                <div class="leaf leaf-2">🍃</div>
                <div class="leaf leaf-3">🌱</div>
            </div>

            <div class="relative z-10 container mx-auto px-4 py-8" style="max-width: 1600px;">
                <!-- Hero Header -->
                <div class="bg-green-600 rounded-3xl shadow-2xl mb-8 overflow-hidden relative p-8">
                    <div class="relative z-10">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div>
                                <h1 class="text-5xl font-bold text-white mb-3 drop-shadow-lg flex items-center gap-3">
                                    <span class="animate-bounce-slow">🍊</span> Espace Investissement
                                </h1>
                                <p class="text-xl text-green-100">Investissez dans l'avenir de PARADISIA et récoltez les fruits du succès 🌴</p>
                            </div>
                            @auth
                            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-2xl p-6 border-2 border-white border-opacity-30">
                                <p class="text-white text-sm mb-1">Votre Capital Investi</p>
                                <p class="text-4xl font-bold text-white">{{ number_format($userInvestment['total'], 0, ',', ' ') }} FCFA</p>
                                <p class="text-green-200 text-sm mt-1 flex items-center gap-1">
                                    <span>🥭</span> {{ number_format($userInvestment['shares'], 0, ',', ' ') }} parts
                                </p>
                            </div>
                            @else
                            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-2xl p-6 border-2 border-white border-opacity-30">
                                <p class="text-white text-sm mb-1">Connectez-vous</p>
                                <p class="text-xl font-bold text-white">Pour voir vos investissements</p>
                                <a href="{{ route('login') }}" class="text-green-200 text-sm mt-1 flex items-center gap-1 hover:underline">
                                    <span>🔐</span> Se connecter
                                </a>
                            </div>
                            @endauth
                        </div>
                    </div>
                    <div class="absolute top-2 right-10 text-7xl opacity-30 animate-swing">🍍</div>
                    <div class="absolute bottom-2 left-10 text-6xl opacity-30 animate-swing-reverse">🥥</div>
                    <div class="absolute top-1/2 right-1/4 text-5xl opacity-20 animate-pulse">🍋</div>
                </div>

                <div class="grid grid-cols-12 gap-6">
                    <!-- Left Section -->
                    <div class="col-span-12 lg:col-span-8 space-y-6">
                        <!-- Quick Stats Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Stat 1 -->
                            <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-orange-200 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 hover:border-orange-400 group">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-14 h-14 bg-orange-100 rounded-xl flex items-center justify-center group-hover:animate-wiggle">
                                        <span class="text-3xl">🍊</span>
                                    </div>
                                    <span class="text-xs bg-orange-100 text-orange-700 px-3 py-1 rounded-full font-bold">Total</span>
                                </div>
                                <h3 class="text-gray-600 text-sm mb-2">Total Investi</h3>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalInvested, 0, ',', ' ') }}</p>
                                <p class="text-xs text-gray-500 mt-1">FCFA</p>
                                @if($monthlyStats['growth'] != 0)
                                <p class="text-sm {{ $monthlyStats['growth'] > 0 ? 'text-green-600' : 'text-red-600' }} mt-2 flex items-center gap-1">
                                    {{ $monthlyStats['growth'] > 0 ? '📈' : '📉' }} {{ $monthlyStats['growth'] > 0 ? '+' : '' }}{{ $monthlyStats['growth'] }}% ce mois
                                </p>
                                @endif
                            </div>

                            <!-- Stat 2 -->
                            <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-yellow-200 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 hover:border-yellow-400 group">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center group-hover:animate-wiggle">
                                        <span class="text-3xl">🍋</span>
                                    </div>
                                    <span class="text-xs bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full font-bold">Actifs</span>
                                </div>
                                <h3 class="text-gray-600 text-sm mb-2">Investisseurs</h3>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalInvestors, 0, ',', ' ') }}</p>
                                <p class="text-sm text-yellow-600 mt-2">Communauté active</p>
                            </div>

                            <!-- Stat 3 -->
                            <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-amber-200 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 hover:border-amber-400 group">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-14 h-14 bg-amber-100 rounded-xl flex items-center justify-center group-hover:animate-wiggle">
                                        <span class="text-3xl">🥭</span>
                                    </div>
                                    <span class="text-xs bg-amber-100 text-amber-700 px-3 py-1 rounded-full font-bold">Parts</span>
                                </div>
                                <h3 class="text-gray-600 text-sm mb-2">Parts Investies</h3>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalShares, 0, ',', ' ') }}</p>
                                <p class="text-sm text-amber-600 mt-2">Total des parts</p>
                            </div>

                            <!-- Stat 4 -->
                            <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-green-200 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 hover:border-green-400 group">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center group-hover:animate-wiggle">
                                        <span class="text-3xl">🍍</span>
                                    </div>
                                    <span class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full font-bold">Global</span>
                                </div>
                                <h3 class="text-gray-600 text-sm mb-2">Pays Investisseurs</h3>
                                <p class="text-2xl font-bold text-gray-900">{{ $countriesCount }}</p>
                                <p class="text-sm text-green-600 mt-2">Présence mondiale</p>
                            </div>
                        </div>

                        <!-- Current Round -->
                        @if($currentRound)
                        <div class="bg-green-600 rounded-2xl shadow-lg p-6 text-white relative overflow-hidden">
                            <div class="absolute right-4 top-1/2 transform -translate-y-1/2 text-8xl opacity-20 animate-pulse">🍇</div>
                            <div class="flex items-center justify-between flex-wrap gap-4 relative z-10">
                                <div>
                                    <h3 class="text-2xl font-bold flex items-center gap-2">
                                        <span class="animate-bounce-slow">🎯</span>
                                        Round Actuel: {{ $currentRound->name }}
                                    </h3>
                                    <p class="text-green-100 mt-2">
                                        Du {{ $currentRound->begin->format('d/m/Y') }} au {{ $currentRound->end->format('d/m/Y') }}
                                    </p>
                                </div>
                                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl p-4">
                                    <p class="text-sm text-green-100">Valeur par part</p>
                                    <p class="text-3xl font-bold">{{ number_format($currentRound->amount, 0, ',', ' ') }} FCFA</p>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="bg-gray-500 rounded-2xl shadow-lg p-6 text-white relative overflow-hidden">
                            <div class="absolute right-4 top-1/2 transform -translate-y-1/2 text-8xl opacity-20">🍂</div>
                            <div class="flex items-center justify-center gap-4">
                                <span class="text-4xl">⏳</span>
                                <div>
                                    <h3 class="text-2xl font-bold">Aucun round actif</h3>
                                    <p class="text-gray-200 mt-1">Le prochain round d'investissement sera bientôt disponible</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- ROI Simulator -->
                        <div class="bg-orange-50 rounded-2xl shadow-lg p-8 border-2 border-orange-200 relative overflow-hidden">
                            <div class="absolute -right-8 -bottom-8 text-9xl opacity-10 rotate-12">🍊</div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2 flex items-center gap-2">
                                <span class="animate-spin-slow">🧮</span>
                                Simulateur de Retour sur Investissement
                            </h3>
                            <p class="text-gray-600 mb-6">Calculez vos gains potentiels avec PARADISIA 🌴</p>

                            <div class="bg-white rounded-xl p-6 mb-6 relative z-10">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-3">💰 Montant à investir (FCFA)</label>
                                        <div class="relative">
                                            <input type="number" id="investAmount" wire:model="investAmount" value="100000" min="10000" step="10000" onchange="calculateROI()" class="w-full px-4 py-4 text-2xl font-bold border-2 border-orange-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                            <span class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 font-semibold">FCFA</span>
                                        </div>
                                        <input type="range" id="investSlider" min="10000" max="5000000" value="100000" step="10000" onchange="updateInvestAmount()" class="w-full mt-4 h-2 bg-orange-200 rounded-lg appearance-none cursor-pointer slider-orange">
                                        <div class="flex justify-between text-xs text-gray-500 mt-2">
                                            <span>10K</span><span>2.5M</span><span>5M</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-3">📅 Durée (années)</label>
                                        <div class="relative">
                                            <input type="number" id="investDuration" wire:model="investDuration" value="3" min="1" max="10" onchange="calculateROI()" class="w-full px-4 py-4 text-2xl font-bold border-2 border-green-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                            <span class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 font-semibold">ans</span>
                                        </div>
                                        <input type="range" id="durationSlider" min="1" max="10" value="3" onchange="updateDuration()" class="w-full mt-4 h-2 bg-green-200 rounded-lg appearance-none cursor-pointer slider-green">
                                        <div class="flex justify-between text-xs text-gray-500 mt-2">
                                            <span>1 an</span><span>5 ans</span><span>10 ans</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-6 p-4 bg-green-50 rounded-xl border-2 border-green-200">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-gray-600 mb-1">Taux de rendement annuel</p>
                                            <p class="text-3xl font-bold text-green-600">18%</p>
                                        </div>
                                        <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center animate-pulse">
                                            <span class="text-3xl">🌱</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 relative z-10">
                                <div class="bg-white rounded-xl p-6 shadow-lg border-2 border-green-200 hover:border-green-400 transition-all group">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-2xl group-hover:animate-bounce">🍊</span>
                                        <p class="text-sm text-gray-600">Capital Final</p>
                                    </div>
                                    <p id="finalCapital" class="text-3xl font-bold text-green-600">157,352 FCFA</p>
                                    <p class="text-xs text-gray-500 mt-2">Avec intérêts composés</p>
                                </div>
                                <div class="bg-white rounded-xl p-6 shadow-lg border-2 border-amber-200 hover:border-amber-400 transition-all group">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-2xl group-hover:animate-bounce">🥭</span>
                                        <p class="text-sm text-gray-600">Gains Totaux</p>
                                    </div>
                                    <p id="totalGains" class="text-3xl font-bold text-amber-600">57,352 FCFA</p>
                                    <p class="text-xs text-gray-500 mt-2">Profit net</p>
                                </div>
                                <div class="bg-white rounded-xl p-6 shadow-lg border-2 border-yellow-200 hover:border-yellow-400 transition-all group">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-2xl group-hover:animate-bounce">🍋</span>
                                        <p class="text-sm text-gray-600">Gains Mensuels</p>
                                    </div>
                                    <p id="monthlyGains" class="text-3xl font-bold text-yellow-600">1,593 FCFA</p>
                                    <p class="text-xs text-gray-500 mt-2">Revenu passif moyen</p>
                                </div>
                            </div>

                            <div class="mt-6 p-6 bg-white rounded-xl border-2 border-orange-200 relative z-10">
                                <h4 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
                                    <span>🌳</span> Projection Détaillée - Croissance de votre investissement
                                </h4>
                                <div class="space-y-3" id="yearlyProjection"></div>
                            </div>
                        </div>

                        <!-- Payment History -->
                        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-green-100 relative overflow-hidden">
                            <div class="absolute -right-4 -top-4 text-8xl opacity-10">🍇</div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                                <span>📜</span> Historique des Investissements <span class="text-2xl animate-bounce-slow">🍎</span>
                            </h3>
                            
                            @if($paymentHistory->count() > 0)
                            <div class="overflow-x-auto relative z-10">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b-2 border-green-200">
                                            <th class="text-left py-3 px-4 text-sm font-bold text-gray-600">Date</th>
                                            <th class="text-left py-3 px-4 text-sm font-bold text-gray-600">Investisseur</th>
                                            <th class="text-left py-3 px-4 text-sm font-bold text-gray-600">Round</th>
                                            <th class="text-right py-3 px-4 text-sm font-bold text-gray-600">Parts</th>
                                            <th class="text-right py-3 px-4 text-sm font-bold text-gray-600">Montant</th>
                                            <th class="text-center py-3 px-4 text-sm font-bold text-gray-600">Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($paymentHistory as $payment)
                                        <tr class="border-b border-gray-100 hover:bg-green-50 transition-colors">
                                            <td class="py-4 px-4 text-sm text-gray-600">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="py-4 px-4">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                                                        {{ strtoupper(substr($payment->user_name ?? 'U', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-semibold text-gray-800">{{ $payment->user_name ?? 'Anonyme' }}</p>
                                                        <p class="text-xs text-gray-500">{{ $payment->user_ref }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-4 px-4 text-sm text-gray-600">{{ $payment->round_name }}</td>
                                            <td class="py-4 px-4 text-sm font-bold text-right text-amber-600">{{ number_format($payment->share, 0, ',', ' ') }}</td>
                                            <td class="py-4 px-4 text-sm font-bold text-right text-green-600">{{ number_format($payment->invested_amount, 0, ',', ' ') }} FCFA</td>
                                            <td class="py-4 px-4 text-center">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">🍀 Confirmé</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-12">
                                <span class="text-6xl animate-bounce-slow">🌱</span>
                                <p class="text-gray-500 mt-4">Aucun investissement enregistré pour le moment</p>
                                <p class="text-sm text-gray-400">Soyez le premier à planter une graine ! 🌴</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Right Sidebar -->
                    <div class="col-span-12 lg:col-span-4 space-y-6">
                        <!-- Quick Invest Card -->
                        <div class="bg-green-600 rounded-2xl shadow-2xl p-6 text-white relative overflow-hidden">
                            <div class="absolute -right-6 -bottom-6 text-9xl opacity-20 rotate-12">🍊</div>
                            <h3 class="text-2xl font-bold mb-4 flex items-center gap-2">
                                <span class="animate-bounce-slow">⚡</span> Investissement Rapide
                            </h3>
                            <p class="text-green-100 text-sm mb-6">Investissez et récoltez les fruits de votre succès 🍇</p>

                            @if($currentRound)
                            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl p-4 mb-4 relative z-10">
                                <label class="block text-sm font-semibold mb-2">💰 Montant (FCFA)</label>
                                <input type="number" wire:model="quickInvestAmount" placeholder="Ex: {{ number_format($currentRound->amount, 0, '', '') }}" class="w-full px-4 py-3 rounded-lg bg-white text-gray-900 font-bold text-xl focus:ring-2 focus:ring-white outline-none">
                            </div>

                            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl p-4 mb-6 relative z-10">
                                <label class="block text-sm font-semibold mb-2">🥭 Nombre de parts</label>
                                <div class="flex items-center gap-3">
                                    <button class="w-12 h-12 bg-white bg-opacity-30 rounded-lg font-bold text-2xl hover:bg-opacity-40 transition-all">-</button>
                                    <input type="number" wire:model="quickSharesCount" min="1" class="flex-1 px-4 py-3 rounded-lg bg-white text-gray-900 font-bold text-xl text-center focus:ring-2 focus:ring-white outline-none">
                                    <button class="w-12 h-12 bg-white bg-opacity-30 rounded-lg font-bold text-2xl hover:bg-opacity-40 transition-all">+</button>
                                </div>
                                <p class="text-xs text-green-100 mt-2">Prix par part: {{ number_format($currentRound->amount, 0, ',', ' ') }} FCFA</p>
                            </div>

                            <button onclick="showInvestMessage()" class="w-full bg-white text-green-600 font-bold py-4 rounded-xl hover:shadow-2xl transition-all transform hover:scale-105 flex items-center justify-center gap-2 relative z-10">
                                <span class="animate-bounce">🚀</span> Investir Maintenant
                            </button>
                            @else
                            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-xl p-6 text-center">
                                <span class="text-4xl animate-pulse">🌱</span>
                                <p class="mt-3 font-semibold">Round non disponible</p>
                                <p class="text-sm text-green-100 mt-2">La prochaine récolte arrive bientôt !</p>
                            </div>
                            @endif

                            <p class="text-xs text-green-100 text-center mt-4 relative z-10">🔒 Paiement sécurisé • 🍃 Retrait à tout moment</p>
                        </div>

                        <!-- Top Investors -->
                        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-yellow-200 relative overflow-hidden">
                            <div class="absolute -right-4 -top-4 text-7xl opacity-10">🏆</div>
                            <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span class="text-2xl animate-bounce-slow">🏆</span> Top Investisseurs <span class="text-xl">🍊</span>
                            </h4>
                            <div class="space-y-3 relative z-10">
                                @forelse($topInvestors as $index => $investor)
                                <div class="flex items-center gap-3 p-3 {{ $index === 0 ? 'bg-yellow-50 border-2 border-yellow-300' : ($index === 1 ? 'bg-gray-50 border-2 border-gray-300' : ($index === 2 ? 'bg-orange-50 border-2 border-orange-300' : 'hover:bg-gray-50 border border-gray-100')) }} rounded-lg transition-all">
                                    <div class="w-10 h-10 {{ $index === 0 ? 'bg-yellow-400' : ($index === 1 ? 'bg-gray-400' : ($index === 2 ? 'bg-orange-400' : 'bg-green-400')) }} rounded-full flex items-center justify-center font-bold text-white">
                                        {{ $index + 1 }}
                                    </div>
                                    @if($investor->photo)
                                    <img src="{{ Storage::url($investor->photo) }}" class="w-10 h-10 rounded-full border-2 {{ $index === 0 ? 'border-yellow-400' : ($index === 1 ? 'border-gray-400' : ($index === 2 ? 'border-orange-400' : 'border-transparent')) }}" alt="">
                                    @else
                                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold border-2 {{ $index === 0 ? 'border-yellow-400' : ($index === 1 ? 'border-gray-400' : ($index === 2 ? 'border-orange-400' : 'border-transparent')) }}">
                                        {{ strtoupper(substr($investor->name ?? 'U', 0, 1)) }}
                                    </div>
                                    @endif
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-800 text-sm">{{ $investor->ref }}</p>
                                        <p class="text-xs text-gray-500">{{ number_format($investor->total_invested, 0, ',', ' ') }} FCFA</p>
                                    </div>
                                    @if($index === 0)
                                    <span class="text-xl animate-bounce-slow">👑</span>
                                    @elseif($index === 1)
                                    <span class="text-xl">🥈</span>
                                    @elseif($index === 2)
                                    <span class="text-xl">🥉</span>
                                    @else
                                    <span class="text-lg">🍊</span>
                                    @endif
                                </div>
                                @empty
                                <div class="text-center py-6 text-gray-500">
                                    <span class="text-4xl animate-bounce-slow">🌱</span>
                                    <p class="mt-2">Aucun investisseur pour le moment</p>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="bg-white rounded-2xl shadow-lg p-6 border-2 border-green-200 relative overflow-hidden">
                            <div class="absolute -right-4 -bottom-4 text-7xl opacity-10">🍃</div>
                            <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span class="text-2xl animate-pulse">⚡</span> Activités Récentes
                            </h4>
                            <div class="space-y-3 relative z-10">
                                @forelse($paymentHistory->take(4) as $activity)
                                <div class="flex items-start gap-3 p-3 bg-green-50 rounded-lg border border-green-100 hover:border-green-300 transition-all">
                                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-white text-sm">🍊</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-800 font-semibold">Nouvel investissement 🌱</p>
                                        <p class="text-xs text-gray-600">{{ $activity->user_ref }} a investi {{ number_format($activity->invested_amount, 0, ',', ' ') }} FCFA</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-6 text-gray-500">
                                    <span class="text-4xl animate-bounce-slow">🍂</span>
                                    <p class="mt-2">Aucune activité récente</p>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Why Invest -->
                        <div class="bg-green-50 rounded-2xl shadow-lg p-6 border-2 border-green-200 relative overflow-hidden">
                            <div class="absolute -right-4 -bottom-4 text-8xl opacity-10">🌴</div>
                            <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span class="text-2xl">💡</span> Pourquoi Investir ?
                            </h4>
                            <div class="space-y-3 relative z-10">
                                <div class="flex items-start gap-3 p-2 hover:bg-white rounded-lg transition-all">
                                    <span class="text-2xl">🍊</span>
                                    <div>
                                        <p class="font-semibold text-gray-800 text-sm">Rendement Garanti</p>
                                        <p class="text-xs text-gray-600">18% de ROI annuel minimum</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-2 hover:bg-white rounded-lg transition-all">
                                    <span class="text-2xl">🛡️</span>
                                    <div>
                                        <p class="font-semibold text-gray-800 text-sm">Sécurisé & Transparent</p>
                                        <p class="text-xs text-gray-600">Contrats certifiés et audités</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-2 hover:bg-white rounded-lg transition-all">
                                    <span class="text-2xl">🥭</span>
                                    <div>
                                        <p class="font-semibold text-gray-800 text-sm">Dividendes Mensuels</p>
                                        <p class="text-xs text-gray-600">Revenus passifs réguliers</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3 p-2 hover:bg-white rounded-lg transition-all">
                                    <span class="text-2xl">🌱</span>
                                    <div>
                                        <p class="font-semibold text-gray-800 text-sm">Impact Écologique</p>
                                        <p class="text-xs text-gray-600">Investissement durable</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Investment Message Modal -->
            <div id="investMessageModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 text-center transform transition-all scale-95 opacity-0 relative overflow-hidden" id="investMessageContent">
                    <div class="absolute -right-8 -top-8 text-9xl opacity-10">🍊</div>
                    <div class="w-20 h-20 bg-green-500 rounded-full mx-auto flex items-center justify-center mb-6 relative z-10">
                        <span class="text-4xl animate-bounce">🚀</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4 relative z-10">Bientôt Disponible ! 🍊</h3>
                    <p class="text-gray-600 mb-6 relative z-10">
                        L'investissement via <span class="font-bold text-green-600">Malpay</span> sera bientôt disponible. 
                        Nous travaillons activement pour vous offrir cette fonctionnalité.
                    </p>
                    <div class="bg-green-50 rounded-xl p-4 mb-6 border-2 border-green-200 relative z-10">
                        <p class="text-sm text-gray-600">📧 Vous serez notifié dès que le service sera opérationnel !</p>
                        <p class="text-xs text-gray-400 mt-2">La récolte arrive bientôt 🌴</p>
                    </div>
                    <button onclick="closeInvestMessage()" class="w-full bg-green-600 text-white font-bold py-4 rounded-xl hover:bg-green-700 hover:shadow-lg transition-all relative z-10">
                        J'ai compris 🍃
                    </button>
                </div>
            </div>

            <style>
                .fruit-float {
                    position: absolute;
                    font-size: 2rem;
                    opacity: 0.4;
                    animation: floatFruit 15s ease-in-out infinite;
                    pointer-events: none;
                }
                .fruit-1 { left: 5%; animation-delay: 0s; animation-duration: 18s; }
                .fruit-2 { left: 15%; animation-delay: 2s; animation-duration: 20s; }
                .fruit-3 { left: 25%; animation-delay: 4s; animation-duration: 16s; }
                .fruit-4 { left: 35%; animation-delay: 1s; animation-duration: 22s; }
                .fruit-5 { left: 45%; animation-delay: 3s; animation-duration: 19s; }
                .fruit-6 { left: 55%; animation-delay: 5s; animation-duration: 17s; }
                .fruit-7 { left: 65%; animation-delay: 2.5s; animation-duration: 21s; }
                .fruit-8 { left: 75%; animation-delay: 1.5s; animation-duration: 18s; }
                .fruit-9 { left: 85%; animation-delay: 4.5s; animation-duration: 20s; }
                .fruit-10 { left: 92%; animation-delay: 0.5s; animation-duration: 16s; }
                .fruit-11 { left: 10%; animation-delay: 6s; animation-duration: 23s; }
                .fruit-12 { left: 80%; animation-delay: 7s; animation-duration: 19s; }
                
                @keyframes floatFruit {
                    0% { transform: translateY(-100px) rotate(0deg); opacity: 0; }
                    10% { opacity: 0.4; }
                    90% { opacity: 0.4; }
                    100% { transform: translateY(calc(100vh + 100px)) rotate(360deg); opacity: 0; }
                }
                
                .leaf {
                    position: absolute;
                    font-size: 3rem;
                    opacity: 0.2;
                    animation: swayLeaf 8s ease-in-out infinite;
                }
                .leaf-1 { right: 10%; top: 20%; animation-delay: 0s; }
                .leaf-2 { right: 5%; top: 50%; animation-delay: 2s; }
                .leaf-3 { left: 3%; top: 70%; animation-delay: 4s; }
                
                @keyframes swayLeaf {
                    0%, 100% { transform: rotate(-10deg) translateX(0); }
                    50% { transform: rotate(10deg) translateX(20px); }
                }
                
                .animate-bounce-slow {
                    animation: bounceSlow 2s ease-in-out infinite;
                }
                @keyframes bounceSlow {
                    0%, 100% { transform: translateY(0); }
                    50% { transform: translateY(-10px); }
                }
                
                .animate-swing {
                    animation: swing 3s ease-in-out infinite;
                }
                .animate-swing-reverse {
                    animation: swingReverse 3.5s ease-in-out infinite;
                }
                @keyframes swing {
                    0%, 100% { transform: rotate(-5deg); }
                    50% { transform: rotate(5deg); }
                }
                @keyframes swingReverse {
                    0%, 100% { transform: rotate(5deg); }
                    50% { transform: rotate(-5deg); }
                }
                
                .animate-wiggle {
                    animation: wiggle 0.5s ease-in-out;
                }
                @keyframes wiggle {
                    0%, 100% { transform: rotate(0deg); }
                    25% { transform: rotate(-10deg); }
                    75% { transform: rotate(10deg); }
                }
                .group:hover .group-hover\:animate-wiggle {
                    animation: wiggle 0.5s ease-in-out;
                }
                
                .animate-spin-slow {
                    animation: spinSlow 4s linear infinite;
                }
                @keyframes spinSlow {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }
                
                .slider-orange::-webkit-slider-thumb {
                    appearance: none;
                    width: 24px;
                    height: 24px;
                    background: #f97316;
                    cursor: pointer;
                    border-radius: 50%;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                }
                .slider-orange::-moz-range-thumb {
                    width: 24px;
                    height: 24px;
                    background: #f97316;
                    cursor: pointer;
                    border-radius: 50%;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                    border: none;
                }
                .slider-green::-webkit-slider-thumb {
                    appearance: none;
                    width: 24px;
                    height: 24px;
                    background: #16a34a;
                    cursor: pointer;
                    border-radius: 50%;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                }
                .slider-green::-moz-range-thumb {
                    width: 24px;
                    height: 24px;
                    background: #16a34a;
                    cursor: pointer;
                    border-radius: 50%;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                    border: none;
                }

                .modal-show { display: flex !important; }
                .modal-content-show { transform: scale(1) !important; opacity: 1 !important; }
            </style>

            <script>
            function calculateROI() {
                const amount = parseFloat(document.getElementById('investAmount').value) || 0;
                const duration = parseInt(document.getElementById('investDuration').value) || 1;
                const rate = 0.18;
                const finalCapital = amount * Math.pow(1 + rate, duration);
                const totalGains = finalCapital - amount;
                const monthlyGains = totalGains / (duration * 12);

                document.getElementById('finalCapital').textContent = Math.round(finalCapital).toLocaleString('fr-FR') + ' FCFA';
                document.getElementById('totalGains').textContent = Math.round(totalGains).toLocaleString('fr-FR') + ' FCFA';
                document.getElementById('monthlyGains').textContent = Math.round(monthlyGains).toLocaleString('fr-FR') + ' FCFA';
                updateYearlyProjection(amount, duration, rate);
            }

            function updateYearlyProjection(amount, duration, rate) {
                const projection = document.getElementById('yearlyProjection');
                projection.innerHTML = '';
                const fruits = ['🍊', '🥭', '🍋', '🍍', '🍇', '🍎', '🍑', '🥝', '🍓', '🫐'];

                for (let year = 1; year <= duration; year++) {
                    const yearCapital = amount * Math.pow(1 + rate, year);
                    const yearGain = yearCapital - amount;
                    const yearReturn = ((yearCapital - amount * Math.pow(1 + rate, year - 1)) / amount * 100);
                    const fruit = fruits[(year - 1) % fruits.length];

                    const div = document.createElement('div');
                    div.className = 'flex items-center justify-between p-3 bg-white rounded-lg border border-green-200 hover:border-green-400 transition-all';
                    div.innerHTML = '<div class="flex items-center gap-2"><span class="text-2xl">' + fruit + '</span><div><p class="font-semibold text-gray-800">Année ' + year + '</p><p class="text-xs text-gray-500">+' + yearReturn.toFixed(1) + '% de croissance</p></div></div><div class="text-right"><p class="font-bold text-green-600">' + Math.round(yearCapital).toLocaleString('fr-FR') + ' FCFA</p><p class="text-xs text-gray-500">Récolte: ' + Math.round(yearGain).toLocaleString('fr-FR') + ' FCFA</p></div>';
                    projection.appendChild(div);
                }
            }

            function updateInvestAmount() {
                const slider = document.getElementById('investSlider');
                const input = document.getElementById('investAmount');
                input.value = slider.value;
                calculateROI();
            }

            function updateDuration() {
                const slider = document.getElementById('durationSlider');
                const input = document.getElementById('investDuration');
                input.value = slider.value;
                calculateROI();
            }

            function showInvestMessage() {
                const modal = document.getElementById('investMessageModal');
                const modalContent = document.getElementById('investMessageContent');
                modal.classList.add('modal-show');
                setTimeout(() => { modalContent.classList.add('modal-content-show'); }, 10);
            }

            function closeInvestMessage() {
                const modal = document.getElementById('investMessageModal');
                const modalContent = document.getElementById('investMessageContent');
                modalContent.classList.remove('modal-content-show');
                setTimeout(() => { modal.classList.remove('modal-show'); }, 300);
            }

            document.addEventListener('DOMContentLoaded', function() {
                calculateROI();
                document.getElementById('investMessageModal').addEventListener('click', function(e) {
                    if (e.target === this) { closeInvestMessage(); }
                });
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') { closeInvestMessage(); }
                });
            });
            </script>
        </div>
    </div>
    @endvolt    
</x-layouts.app>