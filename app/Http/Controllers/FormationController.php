<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Models\Inscription;
use App\Services\WhatsAppNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Support\PageMeta;
use Inertia\Inertia;
use Inertia\Response;

class FormationController extends Controller
{
    /**
     * Liste publique des formations disponibles.
     */
    public function index(): Response
    {
        $formations = Formation::with('images')
            ->where('statut', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($f) => $this->format($f));

        return Inertia::render('formations/index', [
            'formations' => $formations,
        ]);
    }

    /**
     * Détail d'une formation + formulaire d'inscription.
     */
    public function show(Formation $formation): Response
    {
        abort_if($formation->statut !== 'active', 404);

        $formation->load('images');

        $formatted = $this->format($formation, full: true);

        return Inertia::render('formations/show', [
            'formation' => $formatted,
        ])->withViewData([
            'meta' => PageMeta::forFormation($formatted, url()->current()),
        ]);
    }

    /**
     * Enregistre l'inscription d'un visiteur à une formation.
     */
    public function register(Request $request, Formation $formation): RedirectResponse
    {
        abort_if($formation->statut !== 'active', 404);

        $validated = $request->validate([
            'nom'       => 'required|string|max:120',
            'prenom'    => 'required|string|max:120',
            'telephone' => 'nullable|string|max:30',
            'type'      => 'required|in:acceleree,normale',
        ]);

        $inscription = $formation->inscriptions()->create($validated);

        // Alerte WhatsApp aux administrateurs
        WhatsAppNotifier::send(
            "🎓 *Nouvelle inscription Paradisia*\n"
            . "Nom : {$inscription->prenom} {$inscription->nom}\n"
            . ($inscription->telephone ? "Tél : {$inscription->telephone}\n" : '')
            . "Formation : {$formation->titre}\n"
            . "Type : " . ($inscription->type === 'acceleree' ? 'Accélérée' : 'Normale')
        );

        return back()->with('success', 'Votre inscription a bien été enregistrée. Nous vous contacterons bientôt !');
    }

    private function format(Formation $f, bool $full = false): array
    {
        // Galerie : couverture en premier, puis les images additionnelles
        $gallery = collect([$this->mediaUrl($f->image)])
            ->concat($f->images->map(fn ($img) => $this->mediaUrl($img->path)))
            ->filter()
            ->unique()
            ->values();

        return [
            'id'          => $f->id,
            'titre'       => $f->titre,
            'description' => $full ? $f->description : \Illuminate\Support\Str::limit($f->description, 140),
            'prix'        => (float) $f->prix,
            'prix_formatte' => number_format((float) $f->prix, 0, ',', ' ') . ' FCFA',
            'prix_inscription' => (float) $f->prix_inscription,
            'prix_inscription_formatte' => number_format((float) $f->prix_inscription, 0, ',', ' ') . ' FCFA',
            'duree'       => $f->duree,
            'session'     => $f->session,
            'mode'        => $f->mode,
            'mode_label'  => $f->mode === 'en_ligne' ? 'En ligne' : 'En présentiel',
            'image'       => $gallery->first(),
            'images'      => $full ? $gallery : $gallery->take(1),
            'images_count' => $gallery->count(),
            'document'    => $full ? $this->mediaUrl($f->document) : null,
        ];
    }
}
