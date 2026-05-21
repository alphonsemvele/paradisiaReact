<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Publication;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Liste des utilisateurs avec filtres + pagination
     */
    public function index(Request $request): Response
    {
        $search = $request->get('search');
        $role = $request->get('role');
        $status = $request->get('status'); // active, blocked
        $validated = $request->get('validated'); // yes, no
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');

        $query = User::query()
            ->withCount(['publications', 'comments']);

        // Recherche
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        // Filtre rôle
        if ($role) {
            $query->where('role', $role);
        }

        // Filtre statut
        if ($status === 'active') {
            $query->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'Blocked'));
        } elseif ($status === 'blocked') {
            $query->where('status', 'Blocked');
        }

        // Filtre validation
        if ($validated === 'yes') {
            $query->where('valid', 1);
        } elseif ($validated === 'no') {
            $query->where(fn ($q) => $q->where('valid', 0)->orWhereNull('valid'));
        }

        // Tri
        $allowedSorts = ['name', 'email', 'created_at', 'last_active', 'country'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $users = $query->paginate(15)->withQueryString();

        $users->getCollection()->transform(function ($u) {
            return [
                'id' => $u->id,
                'ref' => $u->ref,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'photo' => $u->photo ? asset($u->photo) : null,
                'role' => $u->role,
                'country' => $u->country,
                'country_code' => $u->country_code,
                'valid' => (bool) $u->valid,
                'confirmed' => (bool) $u->confirmed,
                'status' => $u->status,
                'is_blocked' => $u->status === 'Blocked',
                'publications_count' => $u->publications_count,
                'comments_count' => $u->comments_count,
                'last_active_human' => $u->last_active?->diffForHumans(),
                'created_at_human' => $u->created_at->diffForHumans(),
                'created_at_date' => $u->created_at->format('d/m/Y'),
            ];
        });

        // Stats globales
        $stats = [
            'total' => User::count(),
            'today' => User::whereDate('created_at', Carbon::today())->count(),
            'active' => User::where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'Blocked'))->count(),
            'blocked' => User::where('status', 'Blocked')->count(),
            'validated' => User::where('valid', 1)->count(),
            'admins' => User::whereIn('role', ['admin', 'super-admin'])->count(),
        ];

        return Inertia::render('admin/users/index', [
            'users' => $users,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'role' => $role,
                'status' => $status,
                'validated' => $validated,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ],
        ]);
    }

    /**
     * Détail d'un utilisateur
     */
    public function show(User $user): Response
    {
        // Stats utilisateur
        $userStats = [
            'publications' => Publication::where('id_user', $user->id)->count(),
            'comments' => Comment::where('id_user', $user->id)->count(),
            'likes_given' => Like::where('id_user', $user->id)->count(),
            'visits' => Visit::where('id_user', $user->id)->count(),
        ];

        // Dernières publications
        $recentPublications = Publication::where('id_user', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'text' => mb_substr($p->text ?? '', 0, 100),
                'created_at_human' => $p->created_at->diffForHumans(),
            ]);

        // Activité 30 derniers jours
        $activityChart = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $activityChart[] = [
                'label' => $date->format('d/m'),
                'visits' => Visit::where('id_user', $user->id)
                    ->whereDate('created_at', $date)
                    ->count(),
            ];
        }

        return Inertia::render('admin/users/show', [
            'user' => [
                'id' => $user->id,
                'ref' => $user->ref,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'photo' => $user->photo ? asset($user->photo) : null,
                'cover_img' => $user->cover_img ? asset($user->cover_img) : null,
                'role' => $user->role,
                'country' => $user->country,
                'country_code' => $user->country_code,
                'ville' => $user->ville,
                'sexe' => $user->sexe,
                'birth' => $user->birth?->format('Y-m-d'),
                'description' => $user->description,
                'valid' => (bool) $user->valid,
                'confirmed' => (bool) $user->confirmed,
                'status' => $user->status,
                'is_blocked' => $user->status === 'Blocked',
                'referral_code' => $user->referral_code,
                'last_active' => $user->last_active?->format('d/m/Y H:i'),
                'last_active_human' => $user->last_active?->diffForHumans(),
                'created_at' => $user->created_at->format('d/m/Y'),
                'created_at_human' => $user->created_at->diffForHumans(),
            ],
            'userStats' => $userStats,
            'recentPublications' => $recentPublications,
            'activityChart' => $activityChart,
        ]);
    }

    /**
     * Modifier un utilisateur (nom, email, rôle...)
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'nullable|string|in:user,admin,super-admin',
            'country' => 'nullable|string|max:100',
            'ville' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'password' => 'nullable|string|min:8',
        ]);

        // Empêcher de se rétrograder soi-même
        if ($user->id === Auth::id() && isset($validated['role']) && $validated['role'] !== 'super-admin') {
            return back()->withErrors(['error' => 'Vous ne pouvez pas modifier votre propre rôle.']);
        }

        $data = collect($validated)->except('password')->toArray();

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return back()->with('success', 'Utilisateur mis à jour.');
    }

    /**
     * Bloquer / débloquer un utilisateur
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas vous bloquer vous-même.']);
        }

        $user->update([
            'status' => $user->status === 'Blocked' ? 'Active' : 'Blocked',
        ]);

        $msg = $user->status === 'Blocked' ? 'Utilisateur bloqué.' : 'Utilisateur débloqué.';

        return back()->with('success', $msg);
    }

    /**
     * Valider / dévalider un utilisateur
     */
    public function toggleValidation(User $user): RedirectResponse
    {
        $user->update([
            'valid' => $user->valid ? 0 : 1,
        ]);

        $msg = $user->valid ? 'Compte validé.' : 'Validation retirée.';

        return back()->with('success', $msg);
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas vous supprimer vous-même.']);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé.');
    }
}