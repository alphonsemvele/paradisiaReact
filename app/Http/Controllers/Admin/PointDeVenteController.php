<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointDeVente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PointDeVenteController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $query = PointDeVente::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status === 'active') {
            $query->where('status', 'Success');
        } elseif ($status === 'inactive') {
            $query->where('status', '!=', 'Success');
        }

        $points = $query->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        $points->getCollection()->transform(fn ($p) => $this->formatPoint($p));

        $stats = [
            'total' => PointDeVente::count(),
            'active' => PointDeVente::where('status', 'Success')->count(),
            'inactive' => PointDeVente::where('status', '!=', 'Success')->count(),
            'with_location' => PointDeVente::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->count(),
        ];

        return Inertia::render('admin/points-de-vente/index', [
            'points' => $points,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/points-de-vente/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:30',
            'hours' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'image' => 'nullable|image|max:10240',
        ]);

        $data = collect($validated)->except('image')->toArray();
        $data['id_user'] = Auth::id();
        $data['status'] = 'Success';

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadPublicFile($request->file('image'), 'uploads/points-de-vente', 'pdv');
        }

        PointDeVente::create($data);

        return redirect()
            ->route('admin.points-de-vente.index')
            ->with('success', 'Point de vente créé.');
    }

    public function edit(PointDeVente $point_de_vente): Response
    {
        return Inertia::render('admin/points-de-vente/edit', [
            'point' => [
                'id' => $point_de_vente->id,
                'name' => $point_de_vente->name,
                'address' => $point_de_vente->address,
                'phone' => $point_de_vente->phone,
                'hours' => $point_de_vente->hours,
                'latitude' => $point_de_vente->latitude,
                'longitude' => $point_de_vente->longitude,
                'status' => $point_de_vente->status,
                'image' => $this->mediaUrl($point_de_vente->image),
            ],
        ]);
    }

    public function update(Request $request, PointDeVente $point_de_vente): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:30',
            'hours' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'image' => 'nullable|image|max:10240',
            'remove_image' => 'nullable|boolean',
        ]);

        $data = collect($validated)->except(['image', 'remove_image'])->toArray();

        if ($request->boolean('remove_image') && $point_de_vente->image) {
            $this->deletePublicFile($point_de_vente->image);
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            if ($point_de_vente->image) {
                $this->deletePublicFile($point_de_vente->image);
            }
            $data['image'] = $this->uploadPublicFile($request->file('image'), 'uploads/points-de-vente', 'pdv');
        }

        $point_de_vente->update($data);

        return back()->with('success', 'Point de vente mis à jour.');
    }

    public function toggleStatus(PointDeVente $point_de_vente): RedirectResponse
    {
        $point_de_vente->update([
            'status' => $point_de_vente->status === 'Success' ? 'Inactive' : 'Success',
        ]);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function destroy(PointDeVente $point_de_vente): RedirectResponse
    {
        if ($point_de_vente->image) {
            $this->deletePublicFile($point_de_vente->image);
        }

        $point_de_vente->delete();

        return back()->with('success', 'Point de vente supprimé.');
    }

    /* ============ Helpers ============ */

    private function formatPoint(PointDeVente $p): array
    {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'address' => $p->address,
            'phone' => $p->phone,
            'hours' => $p->hours,
            'latitude' => $p->latitude,
            'longitude' => $p->longitude,
            'has_location' => $p->latitude && $p->longitude,
            'status' => $p->status,
            'is_active' => $p->status === 'Success',
            'image' => $this->mediaUrl($p->image),
            'created_at_human' => $p->created_at->diffForHumans(),
            'created_at_date' => $p->created_at->format('d/m/Y'),
        ];
    }


}