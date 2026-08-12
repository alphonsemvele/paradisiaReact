<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Parrainage : mon code / lien à partager, et la liste de MES filleuls.
 * Pour préserver la confidentialité, seuls les CODES des filleuls sont
 * exposés (jamais leur nom, e-mail ou téléphone). L'admin, lui, voit tout.
 */
class ParrainageController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Chaque membre doit avoir un code : on le génère à la volée s'il manque
        // (comptes créés avant le parrainage).
        if (empty($user->ref)) {
            $user->update(['ref' => User::genererRef()]);
        }

        $filleuls = User::where('id_father', $user->id)
            ->orderByDesc('created_at')
            ->get(['ref', 'created_at'])
            ->map(fn (User $f) => [
                // Confidentialité : uniquement le code, jamais les infos perso.
                'code' => $f->ref ?: '—',
                'date' => $f->created_at?->isoFormat('D MMM YYYY'),
            ]);

        return Inertia::render('parrainage/index', [
            'code' => $user->ref,
            'lien' => url('/register?ref='.$user->ref),
            'filleuls' => $filleuls,
            'nb_filleuls' => $filleuls->count(),
        ]);
    }
}
