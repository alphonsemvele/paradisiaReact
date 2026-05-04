<?php
use function Laravel\Folio\{name, middleware};
use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Publication;
use App\Models\Payment;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Support\Facades\DB;

name('admin.dashboard');
// middleware(['auth', 'admin']);
middleware(['auth']);
new class extends Component {
    
    public function getUsersCountProperty()
    {
        return User::count();
    }

    public function getNewUsersThisMonthProperty()
    {
        return User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    public function getPublicationsCountProperty()
    {
        return Publication::where('status', 'Success')->count();
    }

    public function getPaymentsTotalProperty()
    {
        return Payment::where('status', 'Success')->sum('amount');
    }

    public function getPaymentsThisMonthProperty()
    {
        return Payment::where('status', 'Success')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
    }

    public function getCommentsCountProperty()
    {
        return Comment::where('status', 'Success')->count();
    }

    public function getLikesCountProperty()
    {
        return Like::count();
    }

    public function getRecentUsersProperty()
    {
        return User::orderBy('created_at', 'desc')->limit(5)->get();
    }

    public function getRecentPaymentsProperty()
    {
        return Payment::with('user')
            ->where('status', 'Success')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function getRecentPublicationsProperty()
    {
        return Publication::with('user')
            ->where('status', 'Success')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function getMonthlyRevenueProperty()
    {
        return Payment::where('status', 'Success')
            ->whereYear('created_at', now()->year)
            ->selectRaw('MONTH(created_at) as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();
    }

    public function getUserGrowthProperty()
    {
        return User::whereYear('created_at', now()->year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();
    }
};
?>

<x-layouts.admin>
    @volt
    <div class="max-w-full overflow-hidden">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-6 mb-6">
            <!-- Utilisateurs -->
            <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-500">Total Utilisateurs</p>
                        <p class="text-2xl lg:text-3xl font-bold text-gray-800 mt-1">{{ number_format($this->usersCount) }}</p>
                        <p class="text-sm text-green-600 mt-2 flex items-center gap-1">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                            </svg>
                            <span class="truncate">+{{ $this->newUsersThisMonth }} ce mois</span>
                        </p>
                    </div>
                    <div class="w-12 h-12 lg:w-14 lg:h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center flex-shrink-0 ml-3">
                        <svg class="w-6 h-6 lg:w-7 lg:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Revenus -->
            <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-500">Revenus Total</p>
                        <p class="text-2xl lg:text-3xl font-bold text-gray-800 mt-1">
                            <span class="truncate">{{ number_format($this->paymentsTotal) }}</span>
                            <span class="text-base lg:text-lg">F</span>
                        </p>
                        <p class="text-sm text-green-600 mt-2 flex items-center gap-1">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                            </svg>
                            <span class="truncate">{{ number_format($this->paymentsThisMonth) }}F ce mois</span>
                        </p>
                    </div>
                    <div class="w-12 h-12 lg:w-14 lg:h-14 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center flex-shrink-0 ml-3">
                        <svg class="w-6 h-6 lg:w-7 lg:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Publications -->
            <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-500">Publications</p>
                        <p class="text-2xl lg:text-3xl font-bold text-gray-800 mt-1">{{ number_format($this->publicationsCount) }}</p>
                        <p class="text-sm text-blue-600 mt-2 flex items-center gap-1">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                            </svg>
                            <span class="truncate">{{ number_format($this->commentsCount) }} commentaires</span>
                        </p>
                    </div>
                    <div class="w-12 h-12 lg:w-14 lg:h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center flex-shrink-0 ml-3">
                        <svg class="w-6 h-6 lg:w-7 lg:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Engagement -->
            <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-500">Total Likes</p>
                        <p class="text-2xl lg:text-3xl font-bold text-gray-800 mt-1">{{ number_format($this->likesCount) }}</p>
                        <p class="text-sm text-pink-600 mt-2 flex items-center gap-1">
                            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="truncate">Engagement actif</span>
                        </p>
                    </div>
                    <div class="w-12 h-12 lg:w-14 lg:h-14 bg-gradient-to-br from-pink-500 to-pink-600 rounded-2xl flex items-center justify-center flex-shrink-0 ml-3">
                        <svg class="w-6 h-6 lg:w-7 lg:h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 lg:gap-6 mb-6">
            <!-- Revenue Chart -->
            <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Revenus Mensuels</h3>
                        <p class="text-sm text-gray-500">Évolution des revenus en {{ now()->year }}</p>
                    </div>
                    <select class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 w-full sm:w-auto">
                        <option>Cette année</option>
                        <option>Année dernière</option>
                    </select>
                </div>
                <div class="relative" style="height: 250px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Users Growth Chart -->
            <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Croissance Utilisateurs</h3>
                        <p class="text-sm text-gray-500">Nouveaux utilisateurs par mois</p>
                    </div>
                    <select class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 w-full sm:w-auto">
                        <option>Cette année</option>
                        <option>Année dernière</option>
                    </select>
                </div>
                <div class="relative" style="height: 250px;">
                    <canvas id="usersChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tables Row -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 lg:gap-6 mb-6">
            <!-- Recent Users -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 lg:p-5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800">Nouveaux Utilisateurs</h3>
                    <a href="" class="text-sm text-green-600 hover:text-green-700 font-medium whitespace-nowrap">Voir tout →</a>
                </div>
                <div class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
                    @forelse($this->recentUsers as $user)
                    <div class="p-4 flex items-center gap-3 hover:bg-gray-50 transition-all">
                        <img src="{{ $user->photo ? Storage::url($user->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=10b981&color=fff' }}" 
                             alt="{{ $user->name }}" 
                             class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                                {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : ($user->role === 'agent' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($user->role ?? 'user') }}
                            </span>
                            <p class="text-xs text-gray-400 mt-1">{{ $user->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Aucun utilisateur récent
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 lg:p-5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800">Derniers Paiements</h3>
                    <a href="" class="text-sm text-green-600 hover:text-green-700 font-medium whitespace-nowrap">Voir tout →</a>
                </div>
                <div class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
                    @forelse($this->recentPayments as $payment)
                    <div class="p-4 flex items-center gap-3 hover:bg-gray-50 transition-all">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ $payment->user->name ?? 'Utilisateur supprimé' }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $payment->ref ?? 'N/A' }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-bold text-green-600">+{{ number_format($payment->amount) }} F</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $payment->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Aucun paiement récent
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Publications -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 lg:p-5 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">Publications Récentes</h3>
                <a href="" class="text-sm text-green-600 hover:text-green-700 font-medium whitespace-nowrap">Voir tout →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px]">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Auteur</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contenu</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Média</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($this->recentPublications as $publication)
                        <tr class="hover:bg-gray-50 transition-all">
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $publication->user && $publication->user->photo ? Storage::url($publication->user->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($publication->user->name ?? 'U') . '&background=10b981&color=fff' }}" 
                                         alt="" 
                                         class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                                    <span class="text-sm font-medium text-gray-800 truncate max-w-[100px]">{{ $publication->user->name ?? 'Supprimé' }}</span>
                                </div>
                            </td>
                            <td class="px-4 lg:px-6 py-4">
                                <p class="text-sm text-gray-600 truncate max-w-[150px] lg:max-w-[250px]">{{ Str::limit($publication->text, 50) }}</p>
                            </td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                @if($publication->image)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    📷 Image
                                </span>
                                @elseif($publication->video)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    🎥 Vidéo
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    📝 Texte
                                </span>
                                @endif
                            </td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $publication->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    <button class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-all" title="Voir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <button class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                Aucune publication récente
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart');
            if (revenueCtx) {
                const monthlyRevenue = @json($this->monthlyRevenue);
                const months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
                const revenueData = months.map((_, index) => monthlyRevenue[index + 1] || 0);
                
                new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [{
                            label: 'Revenus (FCFA)',
                            data: revenueData,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString() + ' F';
                                    }
                                }
                            },
                            x: {
                                grid: { display: false }
                            }
                        }
                    }
                });
            }

            // Users Growth Chart
            const usersCtx = document.getElementById('usersChart');
            if (usersCtx) {
                const userGrowth = @json($this->userGrowth);
                const months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
                const userData = months.map((_, index) => userGrowth[index + 1] || 0);
                
                new Chart(usersCtx, {
                    type: 'bar',
                    data: {
                        labels: months,
                        datasets: [{
                            label: 'Nouveaux utilisateurs',
                            data: userData,
                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                            borderColor: '#3b82f6',
                            borderWidth: 0,
                            borderRadius: 8,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                ticks: { stepSize: 1 }
                            },
                            x: {
                                grid: { display: false }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
    @endvolt
</x-layouts.admin>