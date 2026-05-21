<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class VisitorController extends Controller
{
    public function index(): Response
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $sevenDaysAgo = Carbon::today()->subDays(7);
        $thirtyDaysAgo = Carbon::today()->subDays(30);

        return Inertia::render('admin/visitors/index', [
            'stats' => [
                // Visites totales
                'total_visits' => Visit::count(),
                'total_unique' => Visit::distinct('session_id')->count('session_id'),

                // Aujourd'hui
                'today_visits' => Visit::whereDate('created_at', $today)->count(),
                'today_unique' => Visit::whereDate('created_at', $today)
                    ->distinct('session_id')
                    ->count('session_id'),

                // Hier
                'yesterday_visits' => Visit::whereDate('created_at', $yesterday)->count(),

                // 7 derniers jours
                'week_visits' => Visit::where('created_at', '>=', $sevenDaysAgo)->count(),
                'week_unique' => Visit::where('created_at', '>=', $sevenDaysAgo)
                    ->distinct('session_id')
                    ->count('session_id'),

                // Visiteurs en ligne (5 dernières minutes)
                'online_now' => Visit::where('created_at', '>=', now()->subMinutes(5))
                    ->distinct('session_id')
                    ->count('session_id'),
            ],

            // Graphique des visites sur 30 jours
            'visitsChart' => $this->getVisitsChart(),

            // Heures de pointe (par heure de la journée, sur 7 jours)
            'hoursChart' => $this->getHoursChart(),

            // Top pages
            'topPages' => $this->getTopPages(),

            // Devices
            'devices' => $this->getDevicesStats(),

            // Top pays
            'topCountries' => $this->getTopCountries(),

            // Top browsers
            'browsers' => $this->getBrowsersStats(),

            // Top OS
            'osStats' => $this->getOSStats(),

            // Visiteurs récents
            'recentVisitors' => $this->getRecentVisitors(),
        ]);
    }

    /**
     * Visites par jour (30 derniers jours)
     */
    private function getVisitsChart(): array
    {
        $data = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            $visits = Visit::whereDate('created_at', $date)->count();
            $unique = Visit::whereDate('created_at', $date)
                ->distinct('session_id')
                ->count('session_id');

            $data[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('d/m'),
                'visits' => $visits,
                'unique' => $unique,
            ];
        }

        return $data;
    }

    /**
     * Visites par heure (moyenne sur 7 derniers jours)
     */
    private function getHoursChart(): array
    {
        $sevenDaysAgo = Carbon::today()->subDays(7);

        $hourlyData = Visit::where('created_at', '>=', $sevenDaysAgo)
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        $data = [];
        for ($h = 0; $h < 24; $h++) {
            $data[] = [
                'hour' => sprintf('%02dh', $h),
                'count' => $hourlyData[$h] ?? 0,
            ];
        }

        return $data;
    }

    /**
     * Top 10 des pages visitées
     */
    private function getTopPages(): array
    {
        return Visit::select('path', DB::raw('COUNT(*) as visits'))
            ->groupBy('path')
            ->orderByDesc('visits')
            ->limit(10)
            ->get()
            ->map(function ($v) {
                return [
                    'path' => '/' . $v->path,
                    'visits' => $v->visits,
                ];
            })
            ->toArray();
    }

    /**
     * Stats devices (mobile / desktop / tablet)
     */
    private function getDevicesStats(): array
    {
        $stats = Visit::select('device_type', DB::raw('COUNT(*) as count'))
            ->groupBy('device_type')
            ->get()
            ->pluck('count', 'device_type')
            ->toArray();

        $total = array_sum($stats) ?: 1;

        return [
            [
                'name' => 'Desktop',
                'icon' => 'monitor',
                'count' => $stats['desktop'] ?? 0,
                'percent' => round((($stats['desktop'] ?? 0) / $total) * 100, 1),
            ],
            [
                'name' => 'Mobile',
                'icon' => 'smartphone',
                'count' => $stats['mobile'] ?? 0,
                'percent' => round((($stats['mobile'] ?? 0) / $total) * 100, 1),
            ],
            [
                'name' => 'Tablet',
                'icon' => 'tablet',
                'count' => $stats['tablet'] ?? 0,
                'percent' => round((($stats['tablet'] ?? 0) / $total) * 100, 1),
            ],
        ];
    }

    /**
     * Top 10 pays
     */
    private function getTopCountries(): array
    {
        return Visit::select('country', 'country_code', DB::raw('COUNT(*) as visits'))
            ->whereNotNull('country')
            ->groupBy('country', 'country_code')
            ->orderByDesc('visits')
            ->limit(10)
            ->get()
            ->map(function ($v) {
                return [
                    'country' => $v->country,
                    'country_code' => $v->country_code,
                    'visits' => $v->visits,
                ];
            })
            ->toArray();
    }

    /**
     * Top navigateurs
     */
    private function getBrowsersStats(): array
    {
        return Visit::select('browser', DB::raw('COUNT(*) as count'))
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->toArray();
    }

    /**
     * Top systèmes d'exploitation
     */
    private function getOSStats(): array
    {
        return Visit::select('os', DB::raw('COUNT(*) as count'))
            ->whereNotNull('os')
            ->groupBy('os')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->toArray();
    }

    /**
     * Visiteurs récents (avec user si connecté)
     */
    private function getRecentVisitors(): array
    {
        return Visit::with('user')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'path' => '/' . $v->path,
                    'ip' => $v->ip_address,
                    'device' => $v->device_type,
                    'browser' => $v->browser,
                    'country' => $v->country,
                    'user' => $v->user ? [
                        'id' => $v->user->id,
                        'name' => $v->user->name,
                        'photo' => $v->user->photo ? asset($v->user->photo) : null,
                    ] : null,
                    'created_at_human' => $v->created_at->diffForHumans(),
                ];
            })
            ->toArray();
    }
}