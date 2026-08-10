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
    public function index(): Response
    {
        $settings = FestySetting::actuel();

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
        $settings = FestySetting::actuel();

        if (! $settings->inscriptions_ouvertes) {
            return response()->json(['ok' => false, 'message' => 'Les inscriptions sont closes.'], 422);
        }

        $validated = $request->validate([
            'festy_team_id' => ['required', 'integer', 'exists:festy_teams,id'],
            'nom' => ['required', 'string', 'max:160'],
            'prenom' => ['required', 'string', 'max:120'],
            'telephone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:180'],
            'ville' => ['nullable', 'string', 'max:120'],
            'quartier' => ['nullable', 'string', 'max:160'],
        ]);

        $team = FestyTeam::where('id', $validated['festy_team_id'])->where('actif', true)->first();

        if (! $team) {
            return response()->json(['ok' => false, 'message' => 'Équipe indisponible.'], 422);
        }

        // Déjà inscrit avec ce numéro : on renvoie son équipe et le groupe.
        $existant = FestyRegistration::where('telephone', $validated['telephone'])->first();

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
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'telephone' => $validated['telephone'],
            'email' => $validated['email'] ?? null,
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
