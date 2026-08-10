<?php

namespace App\Http\Controllers;

use App\Models\FestyRegistration;
use App\Models\FestySetting;
use App\Models\FestyTeam;
use App\Support\PageMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * PARADISIA FESTY côté public : présentation, choix d'équipe, inscription.
 * Après inscription, le lien du groupe WhatsApp de l'équipe est renvoyé.
 */
class FestyController extends Controller
{
    public function index(Request $request): Response
    {
        $settings = FestySetting::actuel();

        // Invité : on mémorise le retour vers Festy après connexion/inscription,
        // pour « suivre » l'utilisateur et lui montrer la suite (choix d'équipe).
        if (! $request->user()) {
            $request->session()->put('url.intended', route('festy.index', ['bienvenue' => 1]));
        }

        $equipes = FestyTeam::where('actif', true)
            ->orderBy('position')
            ->withCount('registrations')
            ->get()
            ->map(fn (FestyTeam $t) => [
                'id' => $t->id,
                'nom' => $t->nom,
                'trait' => $t->trait,
                'couleur' => $t->couleur,
                'emoji' => $t->emoji,
                'image' => $this->mediaUrl($t->image),
                'membres' => $t->registrations_count,
            ]);

        $user = auth()->user();

        // Le profil affiché « en vue » (nom + téléphone du compte).
        $moi = $user ? [
            'nom' => trim($user->name.' '.($user->last_name ?? '')),
            'telephone' => $user->phone,
            'ville' => $user->ville,
        ] : null;

        // Inscription existante de l'utilisateur connecté (statut + groupe).
        $inscription = null;
        if ($user) {
            $r = FestyRegistration::with('team')->where('user_id', $user->id)->first();
            if ($r && $r->team) {
                $inscription = [
                    'equipe' => $r->team->nom,
                    'couleur' => $r->team->couleur,
                    'whatsapp' => $r->team->whatsapp_group,
                ];
            }
        }

        return Inertia::render('festy/index', [
            'festy' => [
                'titre' => $settings->titre,
                'sous_titre' => $settings->sous_titre,
                'date_label' => $settings->date_label,
                'prix' => $settings->prix,
                'description' => $settings->description,
                'inscriptions_ouvertes' => $settings->inscriptions_ouvertes,
            ],
            'equipes' => $equipes,
            'moi' => $moi,
            'inscription' => $inscription,
        ])->withViewData([
            'meta' => PageMeta::make(
                title: $settings->titre.($settings->date_label ? ' — '.$settings->date_label : ''),
                description: $settings->description,
                type: 'website',
            ),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $user = $request->user();

        // Réservé aux membres connectés.
        if (! $user) {
            return response()->json(['ok' => false, 'message' => 'Connectez-vous pour vous inscrire.'], 401);
        }

        $settings = FestySetting::actuel();

        if (! $settings->inscriptions_ouvertes) {
            return response()->json(['ok' => false, 'message' => 'Les inscriptions sont closes.'], 422);
        }

        // On ne demande que l'équipe, la ville et le quartier ; le reste vient
        // du compte (nom, téléphone, e-mail).
        $validated = $request->validate([
            'festy_team_id' => ['required', 'integer', 'exists:festy_teams,id'],
            'ville' => ['nullable', 'string', 'max:120'],
            'quartier' => ['nullable', 'string', 'max:160'],
        ]);

        $team = FestyTeam::where('id', $validated['festy_team_id'])->where('actif', true)->first();

        if (! $team) {
            return response()->json(['ok' => false, 'message' => 'Équipe indisponible.'], 422);
        }

        // Déjà inscrit (identifié par son compte) : on renvoie son équipe.
        $existant = FestyRegistration::where('user_id', $user->id)->first();

        if ($existant) {
            $equipe = $existant->team;

            return response()->json([
                'ok' => true,
                'deja_inscrit' => true,
                'equipe' => $equipe->nom,
                'whatsapp' => $equipe->whatsapp_group,
                'message' => "Vous êtes déjà inscrit dans l'équipe {$equipe->nom}.",
            ]);
        }

        FestyRegistration::create([
            'festy_team_id' => $team->id,
            'user_id' => $user->id,
            'nom' => $user->name,
            'prenom' => $user->last_name,
            'telephone' => $user->phone,
            'email' => $user->email,
            'ville' => $validated['ville'] ?? null,
            'quartier' => $validated['quartier'] ?? null,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'ok' => true,
            'deja_inscrit' => false,
            'equipe' => $team->nom,
            'whatsapp' => $team->whatsapp_group,
            'message' => "Bienvenue dans l'équipe {$team->nom} ! 🎉",
        ], 201);
    }
}
