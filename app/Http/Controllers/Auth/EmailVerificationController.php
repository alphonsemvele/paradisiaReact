<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    /**
     * Confirme l'adresse e-mail depuis le lien signé. Fonctionne même si la
     * personne n'est pas connectée (elle clique le lien dans sa boîte mail).
     */
    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403, 'Lien de confirmation invalide.');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        // On connecte l'utilisateur (compte déjà actif) et on l'envoie à l'accueil.
        Auth::login($user);

        return redirect('/')->with('success', 'Ton adresse e-mail est confirmée ✅');
    }

    /** Renvoie l'e-mail de confirmation (utilisateur connecté). */
    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return back()->with('success', 'E-mail de confirmation renvoyé.');
    }

    /** Renvoi public par adresse (depuis la page de connexion). */
    public function renvoyerPublic(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', strtolower(trim($data['email'])))->first();
        if ($user && ! $user->hasVerifiedEmail()) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                logger()->error('Renvoi confirmation échoué : '.$e->getMessage());
            }
        }

        // Message générique (on ne révèle pas si l'adresse existe).
        return back()->with('status', "Si un compte non confirmé existe pour cette adresse, l'e-mail de confirmation vient d'être renvoyé. Vérifie ta boîte et tes spams.");
    }
}
