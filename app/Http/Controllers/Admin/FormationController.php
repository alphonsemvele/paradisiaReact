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
            'duree'       => $validated['duree'] ?? null,
            'session'     => $validated['session'] ?? null,
            'mode'        => $validated['mode'],
            'statut'      => 'active',
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadFile($request->file('image'), 'img');
        }
        if ($request->hasFile('document')) {
            $data['document'] = $this->uploadFile($request->file('document'), 'doc');
        }

        Formation::create($data);

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
                'duree'       => $formation->duree,
                'session'     => $formation->session,
                'mode'        => $formation->mode,
                'statut'      => $formation->statut,
                'image'       => $formation->image ? asset($formation->image) : null,
                'document'    => $formation->document ? asset($formation->document) : null,
                'document_nom' => $formation->document ? basename($formation->document) : null,
            ],
        ]);
    }

    public function update(Request $request, Formation $formation): RedirectResponse
    {
        $validated = $request->validate($this->rules() + [
            'remove_document' => 'nullable|boolean',
        ]);

        $data = [
            'titre'       => $validated['titre'],
            'description' => $validated['description'] ?? null,
            'prix'        => $validated['prix'],
            'duree'       => $validated['duree'] ?? null,
            'session'     => $validated['session'] ?? null,
            'mode'        => $validated['mode'],
        ];

        if ($request->hasFile('image')) {
            $this->deleteFile($formation->image);
            $data['image'] = $this->uploadFile($request->file('image'), 'img');
        }

        if ($request->boolean('remove_document') && $formation->document) {
            $this->deleteFile($formation->document);
            $data['document'] = null;
        }
        if ($request->hasFile('document')) {
            $this->deleteFile($formation->document);
            $data['document'] = $this->uploadFile($request->file('document'), 'doc');
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
        $this->deleteFile($formation->image);
        $this->deleteFile($formation->document);

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
            'duree'       => 'nullable|string|max:100',
            'session'     => 'nullable|string|max:120',
            'mode'        => 'required|in:presentiel,en_ligne',
            'image'       => 'nullable|image|max:5120',
            'document'    => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:10240',
        ];
    }

    private function format(Formation $f): array
    {
        return [
            'id'                => $f->id,
            'titre'             => $f->titre,
            'description'       => $f->description,
            'prix'              => (float) $f->prix,
            'prix_formatte'     => number_format((float) $f->prix, 0, ',', ' ') . ' FCFA',
            'duree'             => $f->duree,
            'session'           => $f->session,
            'mode'              => $f->mode,
            'mode_label'        => $f->mode === 'en_ligne' ? 'En ligne' : 'En présentiel',
            'image'             => $f->image ? asset($f->image) : null,
            'document'          => $f->document ? asset($f->document) : null,
            'statut'            => $f->statut,
            'is_active'         => $f->statut === 'active',
            'inscriptions_count' => $f->inscriptions_count ?? 0,
            'created_at_date'   => $f->created_at->format('d/m/Y'),
        ];
    }

    private function uploadFile($file, string $prefix): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = $prefix . '_' . uniqid() . '_' . time() . '.' . $extension;
        $folder = 'uploads/formations';

        $destinationPath = public_path($folder);
        if (! file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $file->move($destinationPath, $filename);

        return $folder . '/' . $filename;
    }

    private function deleteFile(?string $path): void
    {
        if (! $path) return;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = parse_url($path, PHP_URL_PATH);
            $path = ltrim($path, '/');
        }

        $fullPath = public_path($path);
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
    }
}
