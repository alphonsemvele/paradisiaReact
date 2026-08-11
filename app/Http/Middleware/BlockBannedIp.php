<?php

namespace App\Http\Middleware;

use App\Models\BannedIp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloque les requêtes venant d'une IP bannie. Les administrateurs connectés
 * ne sont jamais bloqués (pour éviter tout auto-verrouillage).
 */
class BlockBannedIp
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $estAdmin = $user && in_array($user->role, ['admin', 'super-admin'], true);

        if (! $estAdmin && BannedIp::estBannie($request->ip())) {
            abort(403, 'Accès refusé.');
        }

        return $next($request);
    }
}
