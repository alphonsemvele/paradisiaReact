<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Vérifier si l'utilisateur a le rôle admin
        if ($user->role !== 'admin' && $user->role !== 'super-admin') {
            abort(403, 'Accès réservé aux administrateurs.');
        }

        return $next($request);
    }
}