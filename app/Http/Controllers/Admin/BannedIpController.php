<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BannedIp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
