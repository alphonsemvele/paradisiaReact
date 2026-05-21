<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Order;
use App\Models\Product;
use App\Models\Publication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return Inertia::render('admin/dashboard/index', [
            'stats' => [
                'users' => [
                    'total' => User::count(),
                    'today' => User::whereDate('created_at', $today)->count(),
                    'this_month' => User::where('created_at', '>=', $thisMonth)->count(),
                    'last_month' => User::whereBetween('created_at', [
                        $lastMonth,
                        $thisMonth,
                    ])->count(),
                ],
                'publications' => [
                    'total' => Publication::count(),
                    'today' => Publication::whereDate('created_at', $today)->count(),
                    'this_month' => Publication::where('created_at', '>=', $thisMonth)->count(),
                ],
                'products' => [
                    'total' => Product::count(),
                    'active' => Product::where('status', 'Success')->count(),
                ],
                'engagement' => [
                    'likes' => Like::count(),
                    'comments' => Comment::count(),
                    'today_likes' => Like::whereDate('created_at', $today)->count(),
                    'today_comments' => Comment::whereDate('created_at', $today)->count(),
                ],
            ],

            // Graphique : nouvelles inscriptions sur 30 jours
            'usersChart' => $this->getUsersChart(),

            // Graphique : activité (likes + comments) sur 30 jours
            'activityChart' => $this->getActivityChart(),

            // Derniers utilisateurs inscrits
            'recentUsers' => User::orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['id', 'name', 'email', 'photo', 'country', 'created_at'])
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'photo' => $user->photo ? asset($user->photo) : null,
                        'country' => $user->country,
                        'created_at_human' => $user->created_at->diffForHumans(),
                    ];
                }),

            // Dernières publications
            'recentPublications' => Publication::with('user')
                ->where('status', 'Success')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($pub) {
                    return [
                        'id' => $pub->id,
                        'text' => mb_substr($pub->text ?? '', 0, 80),
                        'user_name' => $pub->user?->name ?? 'Utilisateur',
                        'created_at_human' => $pub->created_at->diffForHumans(),
                    ];
                }),
        ]);
    }

    /**
     * Inscriptions par jour (30 derniers jours)
     */
    private function getUsersChart(): array
    {
        $data = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('d/m'),
                'count' => User::whereDate('created_at', $date)->count(),
            ];
        }

        return $data;
    }

    /**
     * Activité (likes + comments) sur 30 jours
     */
    private function getActivityChart(): array
    {
        $data = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            $data[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('d/m'),
                'likes' => Like::whereDate('created_at', $date)->count(),
                'comments' => Comment::whereDate('created_at', $date)->count(),
            ];
        }

        return $data;
    }
}