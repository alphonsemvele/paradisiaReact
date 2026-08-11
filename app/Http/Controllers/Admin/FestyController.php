<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BannedIp;
use App\Models\FestyRegistration;
use App\Models\FestySetting;
use App\Models\FestyTeam;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Administration de PARADISIA FESTY : réglages, équipes (dont le lien du
 * groupe WhatsApp) et liste des inscrits.
 */
class FestyController extends Controller
{
    public function index(): Response
    {
        $settings = FestySetting::actuel();

        $equipes = FestyTeam::orderBy('position')
            ->withCount('registrations')
            ->get()
            ->map(fn (FestyTeam $t) => [
                'id' => $t->id,
                'nom' => $t->nom,
                'trait' => $t->trait,
                'couleur' => $t->couleur,
                'emoji' => $t->emoji,
                'whatsapp_group' => $t->whatsapp_group,
                'actif' => $t->actif,
                'membres' => $t->registrations_count,
            ]);

        return Inertia::render('admin/festy/index', [
            'settings' => $settings->only(['titre', 'sous_titre', 'date_label', 'prix', 'description', 'inscriptions_ouvertes']),
            'equipes' => $equipes,
            'stats' => [
                'inscrits' => FestyRegistration::count(),
                'equipes' => FestyTeam::where('actif', true)->count(),
                'sans_groupe' => FestyTeam::where('actif', true)->whereNull('whatsapp_group')->count(),
            ],
        ]);
    }

    /** Enregistre les réglages généraux. */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:120'],
            'sous_titre' => ['nullable', 'string', 'max:255'],
            'date_label' => ['nullable', 'string', 'max:120'],
            'prix' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'inscriptions_ouvertes' => ['boolean'],
        ]);

        FestySetting::actuel()->update($validated);

        return back()->with('success', 'Réglages enregistrés.');
    }

    public function storeTeam(Request $request): RedirectResponse
    {
        FestyTeam::create($this->validerTeam($request) + [
            'position' => (int) FestyTeam::max('position') + 1,
        ]);

        return back()->with('success', 'Équipe ajoutée.');
    }

    public function updateTeam(Request $request, FestyTeam $team): RedirectResponse
    {
        $data = $this->validerTeam($request);

        if ($request->hasFile('image')) {
            $this->deletePublicFile($team->image);
            $data['image'] = $this->uploadPublicFile($request->file('image'), 'uploads/festy', 'team');
        }

        $team->update($data);

        return back()->with('success', 'Équipe mise à jour.');
    }

    public function destroyTeam(FestyTeam $team): RedirectResponse
    {
        $this->deletePublicFile($team->image);
        $team->delete();

        return back()->with('success', 'Équipe supprimée.');
    }

    /** Liste des inscrits, filtrable par équipe. */
    public function registrations(Request $request): Response
    {
        $teamId = $request->integer('equipe') ?: null;

        $query = FestyRegistration::with('team')->latest();
        if ($teamId) {
            $query->where('festy_team_id', $teamId);
        }

        $inscrits = $query->get()->map(fn (FestyRegistration $r) => [
            'id' => $r->id,
            'nom' => $r->nom,
            'prenom' => $r->prenom,
            'telephone' => $r->telephone,
            'email' => $r->email,
            'ville' => $r->ville,
            'quartier' => $r->quartier,
            'festy_team_id' => $r->festy_team_id,
            'equipe' => $r->team?->nom,
            'ip' => $r->ip,
            'date' => $r->created_at->isoFormat('D MMM YYYY [à] HH:mm'),
        ]);

        return Inertia::render('admin/festy/inscrits', [
            'inscrits' => $inscrits,
            'equipes' => FestyTeam::orderBy('position')->get(['id', 'nom'])
                ->map(fn ($t) => ['id' => $t->id, 'nom' => $t->nom]),
            'filtre' => $teamId,
            'total' => FestyRegistration::count(),
            'par_equipe' => FestyTeam::orderBy('position')
                ->withCount('registrations')
                ->get()
                ->map(fn ($t) => ['nom' => $t->nom, 'couleur' => $t->couleur, 'membres' => $t->registrations_count]),
        ]);
    }

    /** Modifie un inscrit (équipe, coordonnées). */
    public function updateRegistration(Request $request, FestyRegistration $registration): RedirectResponse
    {
        $validated = $request->validate([
            'festy_team_id' => ['required', 'integer', 'exists:festy_teams,id'],
            'nom' => ['nullable', 'string', 'max:160'],
            'prenom' => ['nullable', 'string', 'max:120'],
            'telephone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:180'],
            'ville' => ['nullable', 'string', 'max:120'],
            'quartier' => ['nullable', 'string', 'max:160'],
        ]);

        $registration->update($validated);

        return back()->with('success', 'Inscrit mis à jour.');
    }

    /** Supprime un inscrit. */
    public function destroyRegistration(FestyRegistration $registration): RedirectResponse
    {
        $registration->delete();

        return back()->with('success', 'Inscrit supprimé.');
    }

    /**
     * Bloque et supprime définitivement le compte lié à cet inscrit
     * (faux comptes / doublons). Repli : blocage si la suppression échoue.
     */
    public function destroyAccount(FestyRegistration $registration): RedirectResponse
    {
        $user = $registration->user_id ? User::find($registration->user_id) : null;

        $registration->delete();

        if (! $user) {
            return back()->with('success', 'Inscription supprimée (aucun compte lié).');
        }

        $nom = trim($user->name.' '.($user->last_name ?? ''));

        try {
            $user->delete();

            return back()->with('success', "Compte de {$nom} supprimé définitivement.");
        } catch (\Throwable $e) {
            // Repli : on bloque le compte s'il ne peut pas être supprimé.
            $user->update(['valid' => 0, 'confirmed' => 0, 'is_blocked' => 1]);

            return back()->with('success', "Compte de {$nom} bloqué (suppression impossible).");
        }
    }

    /** Bannit l'adresse IP d'un inscrit (faux comptes / spam). */
    public function bannirIp(Request $request, FestyRegistration $registration): RedirectResponse
    {
        if (! $registration->ip) {
            return back()->withErrors(['ip' => 'Aucune IP enregistrée pour cet inscrit.']);
        }

        if ($registration->ip === $request->ip()) {
            return back()->withErrors(['ip' => 'Vous ne pouvez pas bannir votre propre adresse IP.']);
        }

        BannedIp::firstOrCreate(
            ['ip' => $registration->ip],
            ['raison' => 'Festy — '.($registration->nom ?? 'inscrit').' ('.($registration->email ?? '—').')'],
        );

        return back()->with('success', "IP {$registration->ip} bannie.");
    }

    /** Export CSV des inscrits (Excel). */
    public function export(Request $request)
    {
        $rows = FestyRegistration::with('team')->orderBy('festy_team_id')->latest()->get();

        $csv = "Équipe;Nom;Prénom;Téléphone;E-mail;Ville;Quartier;Date\n";
        foreach ($rows as $r) {
            $csv .= implode(';', [
                $r->team?->nom,
                str_replace(';', ',', (string) $r->nom),
                str_replace(';', ',', (string) $r->prenom),
                $r->telephone,
                $r->email,
                str_replace(';', ',', (string) $r->ville),
                str_replace(';', ',', (string) $r->quartier),
                $r->created_at->format('d/m/Y H:i'),
            ])."\n";
        }

        // BOM pour l'accentuation correcte dans Excel.
        return response("\xEF\xBB\xBF".$csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="inscrits-festy.csv"',
        ]);
    }

    private function validerTeam(Request $request): array
    {
        return $request->validate([
            'nom' => ['required', 'string', 'max:80'],
            'trait' => ['nullable', 'string', 'max:120'],
            'couleur' => ['required', 'string', 'max:20'],
            'emoji' => ['nullable', 'string', 'max:16'],
            'whatsapp_group' => ['nullable', 'string', 'max:500'],
            'actif' => ['boolean'],
        ]);
    }
}
