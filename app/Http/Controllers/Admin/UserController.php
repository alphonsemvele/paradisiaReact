<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BannedIp;
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

        // Filtre statut (blocage)
        if ($status === 'active') {
            $query->where(fn ($q) => $q->where('is_blocked', 0)->orWhereNull('is_blocked'));
        } elseif ($status === 'blocked') {
            $query->where('is_blocked', 1);
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

        // Indice de doublons : numéros / e-mails partagés par plusieurs comptes.
        $pagePhones = $users->getCollection()->pluck('phone')->filter()->unique()->values();
        $pageEmails = $users->getCollection()->pluck('email')->filter()->unique()->values();

        $dupPhones = $pagePhones->isEmpty() ? [] : User::whereIn('phone', $pagePhones)
            ->groupBy('phone')->havingRaw('COUNT(*) > 1')->pluck('phone')->all();
        $dupEmails = $pageEmails->isEmpty() ? [] : User::whereIn('email', $pageEmails)
            ->groupBy('email')->havingRaw('COUNT(*) > 1')->pluck('email')->all();

        $users->getCollection()->transform(function ($u) use ($dupPhones, $dupEmails) {
            return [
                'id' => $u->id,
                'ref' => $u->ref,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'photo' => $this->mediaUrl($u->photo),
                'role' => $u->role,
                'country' => $u->country,
                'country_code' => $u->country_code,
                'valid' => (bool) $u->valid,
                'confirmed' => (bool) $u->confirmed,
                'status' => $u->status,
                'is_blocked' => (bool) $u->is_blocked,
                'phone_partage' => $u->phone && in_array($u->phone, $dupPhones, true),
                'email_partage' => $u->email && in_array($u->email, $dupEmails, true),
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
            'active' => User::where(fn ($q) => $q->where('is_blocked', 0)->orWhereNull('is_blocked'))->count(),
            'blocked' => User::where('is_blocked', 1)->count(),
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

        // Historique de connexion : IP distinctes utilisées par l'utilisateur.
        $connexions = Visit::where('id_user', $user->id)
            ->whereNotNull('ip_address')
            ->selectRaw('ip_address, COUNT(*) as n, MAX(created_at) as derniere')
            ->groupBy('ip_address')
            ->orderByDesc('derniere')
            ->limit(50)
            ->get()
            ->map(fn ($v) => [
                'ip' => $v->ip_address,
                'nombre' => (int) $v->n,
                'derniere' => Carbon::parse($v->derniere)->isoFormat('D MMM YYYY [à] HH:mm'),
                'bannie' => BannedIp::estBannie($v->ip_address),
            ]);

        // Comptes liés : même numéro de téléphone ou même e-mail.
        $comptesLies = collect();
        if ($user->phone || $user->email) {
            $comptesLies = User::where('id', '<>', $user->id)
                ->where(function ($q) use ($user) {
                    if ($user->phone) {
                        $q->where('phone', $user->phone);
                    }
                    if ($user->email) {
                        $q->orWhere('email', $user->email);
                    }
                })
                ->limit(30)
                ->get(['id', 'name', 'email', 'phone', 'is_blocked'])
                ->map(fn ($u) => [
                    'id' => $u->id,
                    'nom' => $u->name,
                    'email' => $u->email,
                    'phone' => $u->phone,
                    'bloque' => (bool) $u->is_blocked,
                    'meme_phone' => $user->phone && $u->phone === $user->phone,
                    'meme_email' => $user->email && $u->email === $user->email,
                ]);
        }

        return Inertia::render('admin/users/show', [
            'connexions' => $connexions,
            'comptesLies' => $comptesLies,
            'user' => [
                'id' => $user->id,
                'ref' => $user->ref,
                'name' => $user->name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'photo' => $this->mediaUrl($user->photo),
                'cover_img' => $this->mediaUrl($user->cover_img),
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
                'is_blocked' => (bool) $user->is_blocked,
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
            'is_blocked' => ! $user->is_blocked,
        ]);

        $msg = $user->is_blocked ? 'Utilisateur bloqué.' : 'Utilisateur débloqué.';

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