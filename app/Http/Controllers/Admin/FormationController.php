<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FormationController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');

        $query = Formation::withCount('inscriptions');

        if ($search) {
            $query->where('titre', 'like', "%{$search}%");
        }

        $formations = $query->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        $formations->getCollection()->transform(fn ($f) => $this->format($f));

        $stats = [
            'total'        => Formation::count(),
            'active'       => Formation::where('statut', 'active')->count(),
            'inscriptions' => \App\Models\Inscription::count(),
            'this_month'   => Formation::where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
        ];

        return Inertia::render('admin/formations/index', [
            'formations' => $formations,
            'stats'      => $stats,
            'filters'    => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/formations/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $data = [
            'titre'       => $validated['titre'],
            'description' => $validated['description'] ?? null,
            'prix'        => $validated['prix'],
            'prix_inscription' => $validated['prix_inscription'] ?? 0,
            'duree'       => $validated['duree'] ?? null,
            'session'     => $validated['session'] ?? null,
            'mode'        => $validated['mode'],
            'statut'      => 'active',
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadPublicFile($request->file('image'), 'uploads/formations', 'img');
        }
        if ($request->hasFile('document')) {
            $data['document'] = $this->uploadPublicFile($request->file('document'), 'uploads/formations', 'doc');
        }

        $formation = Formation::create($data);

        $this->storeGalleryImages($request, $formation);

        return redirect()
            ->route('admin.formations.index')
            ->with('success', 'Formation créée.');
    }

    public function edit(Formation $formation): Response
    {
        return Inertia::render('admin/formations/edit', [
            'formation' => [
                'id'          => $formation->id,
                'titre'       => $formation->titre,
                'description' => $formation->description,
                'prix'        => $formation->prix,
                'prix_inscription' => $formation->prix_inscription,
                'duree'       => $formation->duree,
                'session'     => $formation->session,
                'mode'        => $formation->mode,
                'statut'      => $formation->statut,
                'image'       => $this->mediaUrl($formation->image),
                'document'    => $this->mediaUrl($formation->document),
                'document_nom' => $formation->document ? basename($formation->document) : null,
                'images'      => $formation->images
                    ->map(fn ($img) => ['id' => $img->id, 'url' => $this->mediaUrl($img->path)])
                    ->filter(fn ($img) => $img['url'])
                    ->values(),
            ],
        ]);
    }

    public function update(Request $request, Formation $formation): RedirectResponse
    {
        $validated = $request->validate($this->rules() + [
            'remove_document' => 'nullable|boolean',
            'remove_images'   => 'nullable|array',
            'remove_images.*' => 'integer',
        ]);

        $data = [
            'titre'       => $validated['titre'],
            'description' => $validated['description'] ?? null,
            'prix'        => $validated['prix'],
            'prix_inscription' => $validated['prix_inscription'] ?? 0,
            'duree'       => $validated['duree'] ?? null,
            'session'     => $validated['session'] ?? null,
            'mode'        => $validated['mode'],
        ];

        // Retrait d'images de la galerie
        foreach ($formation->images()->whereIn('id', $validated['remove_images'] ?? [])->get() as $img) {
            $this->deletePublicFile($img->path);
            $img->delete();
        }

        // Nouvelles images de galerie
        $this->storeGalleryImages($request, $formation);

        if ($request->hasFile('image')) {
            $this->deletePublicFile($formation->image);
            $data['image'] = $this->uploadPublicFile($request->file('image'), 'uploads/formations', 'img');
        }

        if ($request->boolean('remove_document') && $formation->document) {
            $this->deletePublicFile($formation->document);
            $data['document'] = null;
        }
        if ($request->hasFile('document')) {
            $this->deletePublicFile($formation->document);
            $data['document'] = $this->uploadPublicFile($request->file('document'), 'uploads/formations', 'doc');
        }

        $formation->update($data);

        return redirect()
            ->route('admin.formations.index')
            ->with('success', 'Formation mise à jour.');
    }

    public function toggleStatus(Formation $formation): RedirectResponse
    {
        $formation->update([
            'statut' => $formation->statut === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(Formation $formation): RedirectResponse
    {
        $this->deletePublicFile($formation->image);
        $this->deletePublicFile($formation->document);

        foreach ($formation->images as $img) {
            $this->deletePublicFile($img->path);
        }

        $formation->delete();

        return back()->with('success', 'Formation supprimée.');
    }

    /* ============ Helpers ============ */

    private function rules(): array
    {
        return [
            'titre'       => 'required|string|max:180',
            'description' => 'nullable|string|max:5000',
            'prix'        => 'required|numeric|min:0',
            'prix_inscription' => 'nullable|numeric|min:0',
            'duree'       => 'nullable|string|max:100',
            'session'     => 'nullable|string|max:120',
            'mode'        => 'required|in:presentiel,en_ligne',
            'image'       => 'nullable|image|max:10240',
            'images'      => 'nullable|array|max:8',
            'images.*'    => 'image|max:10240',
            'document'    => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:10240',
        ];
    }

    /** Enregistre les images de galerie envoyées (champ images[]). */
    private function storeGalleryImages(Request $request, Formation $formation): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $position = (int) $formation->images()->max('position');

        foreach ($request->file('images') as $file) {
            $formation->images()->create([
                'path' => $this->uploadPublicFile($file, 'uploads/formations', 'img'),
                'position' => ++$position,
            ]);
        }
    }

    private function format(Formation $f): array
    {
        return [
            'id'                => $f->id,
            'titre'             => $f->titre,
            'description'       => $f->description,
            'prix'              => (float) $f->prix,
            'prix_formatte'     => number_format((float) $f->prix, 0, ',', ' ') . ' FCFA',
            'prix_inscription'  => (float) $f->prix_inscription,
            'prix_inscription_formatte' => number_format((float) $f->prix_inscription, 0, ',', ' ') . ' FCFA',
            'duree'             => $f->duree,
            'session'           => $f->session,
            'mode'              => $f->mode,
            'mode_label'        => $f->mode === 'en_ligne' ? 'En ligne' : 'En présentiel',
            'image'             => $this->mediaUrl($f->image),
            'document'          => $this->mediaUrl($f->document),
            'statut'            => $f->statut,
            'is_active'         => $f->statut === 'active',
            'inscriptions_count' => $f->inscriptions_count ?? 0,
            'created_at_date'   => $f->created_at->format('d/m/Y'),
        ];
    }


}
