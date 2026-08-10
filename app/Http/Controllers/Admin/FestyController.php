<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FestyRegistration;
use App\Models\FestySetting;
use App\Models\FestyTeam;
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
            'telephone' => $r->telephone,
            'email' => $r->email,
            'ville' => $r->ville,
            'equipe' => $r->team?->nom,
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

    /** Export CSV des inscrits (Excel). */
    public function export(Request $request)
    {
        $rows = FestyRegistration::with('team')->orderBy('festy_team_id')->latest()->get();

        $csv = "Équipe;Nom;Téléphone;E-mail;Ville;Date\n";
        foreach ($rows as $r) {
            $csv .= implode(';', [
                $r->team?->nom,
                str_replace(';', ',', (string) $r->nom),
                $r->telephone,
                $r->email,
                str_replace(';', ',', (string) $r->ville),
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
