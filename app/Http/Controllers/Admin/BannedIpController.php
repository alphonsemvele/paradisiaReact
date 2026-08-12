<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BannedIp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gestion des adresses IP bannies (bloquées par le middleware BlockBannedIp).
 */
class BannedIpController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/securite/ips', [
            'ips' => BannedIp::latest()->get()->map(fn (BannedIp $b) => [
                'id' => $b->id,
                'ip' => $b->ip,
                'raison' => $b->raison,
                'date' => $b->created_at->isoFormat('D MMM YYYY [à] HH:mm'),
            ]),
        ]);
    }

    /**
     * Historique de connexion agrégé par adresse IP (90 derniers jours) :
     * pour chaque IP, le nombre de visites, les comptes associés et la
     * dernière activité. Permet de repérer les fermes de faux comptes.
     */
    public function connexions(Request $request): Response
    {
        $recherche = trim((string) $request->get('q', ''));
        $tri = $request->get('tri', 'recent'); // recent | ancien | comptes
        $depuis = Carbon::now()->subDays(90);

        $base = DB::table('visits')
            ->whereNotNull('ip_address')
            ->where('created_at', '>=', $depuis);

        if ($recherche !== '') {
            $base->where('ip_address', 'like', '%'.$recherche.'%');
        }

        $ips = (clone $base)
            ->selectRaw('ip_address, COUNT(*) as visites, COUNT(DISTINCT id_user) as comptes, MAX(created_at) as derniere')
            ->groupBy('ip_address')
            ->when($tri === 'comptes', fn ($q) => $q->orderByDesc('comptes')->orderByDesc('derniere'))
            ->when($tri === 'ancien', fn ($q) => $q->orderBy('derniere'))
            ->when(! in_array($tri, ['comptes', 'ancien'], true), fn ($q) => $q->orderByDesc('derniere'))
            ->limit(200)
            ->get();

        // Comptes associés à chaque IP (2 requêtes, pas de N+1).
        $liste = $ips->pluck('ip_address');
        $comptesParIp = DB::table('visits')
            ->join('users', 'users.id', '=', 'visits.id_user')
            ->whereIn('visits.ip_address', $liste)
            ->where('visits.created_at', '>=', $depuis)
            ->whereNotNull('visits.id_user')
            ->select('visits.ip_address', 'users.id', 'users.name', 'users.email', 'users.status')
            ->distinct()
            ->get()
            ->groupBy('ip_address');

        $bannies = BannedIp::ensemble();

        $connexions = $ips->map(fn ($r) => [
            'ip' => $r->ip_address,
            'visites' => (int) $r->visites,
            'comptes' => (int) $r->comptes,
            'derniere' => Carbon::parse($r->derniere)->isoFormat('D MMM YYYY [à] HH:mm'),
            'bannie' => in_array($r->ip_address, $bannies, true),
            'utilisateurs' => ($comptesParIp[$r->ip_address] ?? collect())
                ->take(12)
                ->map(fn ($u) => [
                    'id' => $u->id,
                    'nom' => $u->name,
                    'email' => $u->email,
                    'bloque' => $u->status === 'Blocked',
                ])->values(),
        ]);

        return Inertia::render('admin/securite/connexions', [
            'connexions' => $connexions,
            'recherche' => $recherche,
            'tri' => $tri,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ip' => ['required', 'ip'],
            'raison' => ['nullable', 'string', 'max:255'],
        ], [
            'ip.ip' => "L'adresse IP n'est pas valide.",
        ]);

        if ($validated['ip'] === $request->ip()) {
            return back()->withErrors(['ip' => 'Vous ne pouvez pas bannir votre propre adresse IP.']);
        }

        BannedIp::firstOrCreate(
            ['ip' => $validated['ip']],
            ['raison' => $validated['raison'] ?? null],
        );

        return back()->with('success', "IP {$validated['ip']} bannie.");
    }

    public function destroy(BannedIp $bannedIp): RedirectResponse
    {
        $ip = $bannedIp->ip;
        $bannedIp->delete();

        return back()->with('success', "IP {$ip} débannie.");
    }
}
