<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Matériel & stock : cartons, bouteilles, matières premières… */
class StockController extends Controller
{
    private const TYPES = ['carton', 'bouteille', 'matiere_premiere', 'autre'];

    public function index(Request $request): Response
    {
        $type = $request->get('type');

        $query = Material::query()->orderBy('type')->orderBy('nom');
        if (in_array($type, self::TYPES, true)) {
            $query->where('type', $type);
        }

        $materiels = $query->get()->map(fn (Material $m) => [
            'id' => $m->id,
            'nom' => $m->nom,
            'type' => $m->type,
            'unite' => $m->unite,
            'stock' => (float) $m->stock,
            'seuil_alerte' => (float) $m->seuil_alerte,
            'consignable' => $m->consignable,
            'actif' => $m->actif,
            'alerte' => $m->enAlerte(),
        ]);

        return Inertia::render('admin/stock/index', [
            'materiels' => $materiels,
            'filtre' => $type,
            'stats' => [
                'total' => Material::count(),
                'alertes' => Material::whereColumn('stock', '<=', 'seuil_alerte')->where('seuil_alerte', '>', 0)->count(),
                'cartons' => Material::where('type', 'carton')->sum('stock'),
                'bouteilles' => Material::where('type', 'bouteille')->sum('stock'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->valider($request);
        // Le stock initial passe par un mouvement d'ajustement pour tracer l'origine.
        $stockInitial = (float) ($request->input('stock', 0));
        $data['stock'] = 0;
        $m = Material::create($data);

        if ($stockInitial != 0.0) {
            Material::mouvement($m->id, 'ajustement', $stockInitial, ['motif' => 'Stock initial']);
        }

        return back()->with('success', 'Matériel ajouté.');
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        // On ne touche pas au stock ici (il ne se modifie que par mouvement).
        $data = $this->valider($request);
        unset($data['stock']);
        $material->update($data);

        return back()->with('success', 'Matériel mis à jour.');
    }

    public function destroy(Material $material): RedirectResponse
    {
        $material->delete();

        return back()->with('success', 'Matériel supprimé.');
    }

    /** Ajustement manuel du stock (inventaire, correction). */
    public function ajuster(Request $request, Material $material): RedirectResponse
    {
        $data = $request->validate([
            'quantite' => ['required', 'numeric'],       // signée : + ou -
            'motif' => ['nullable', 'string', 'max:255'],
        ]);

        Material::mouvement($material->id, 'ajustement', (float) $data['quantite'], [
            'motif' => $data['motif'] ?? 'Ajustement manuel',
        ]);

        return back()->with('success', 'Stock ajusté.');
    }

    /** Historique des mouvements d'un matériel. */
    public function historique(Material $material): Response
    {
        $mouvements = $material->movements()->latest('id')->limit(200)->get()->map(fn (StockMovement $mv) => [
            'id' => $mv->id,
            'type' => $mv->type,
            'quantite' => (float) $mv->quantite,
            'stock_apres' => $mv->stock_apres !== null ? (float) $mv->stock_apres : null,
            'motif' => $mv->motif,
            'date' => $mv->created_at?->isoFormat('D MMM YYYY [à] HH:mm'),
        ]);

        return Inertia::render('admin/stock/historique', [
            'materiel' => ['id' => $material->id, 'nom' => $material->nom, 'unite' => $material->unite, 'stock' => (float) $material->stock],
            'mouvements' => $mouvements,
        ]);
    }

    private function valider(Request $request): array
    {
        return $request->validate([
            'nom' => ['required', 'string', 'max:160'],
            'type' => ['required', 'in:'.implode(',', self::TYPES)],
            'unite' => ['required', 'string', 'max:20'],
            'stock' => ['nullable', 'numeric'],
            'seuil_alerte' => ['nullable', 'numeric', 'min:0'],
            'consignable' => ['boolean'],
            'actif' => ['boolean'],
        ]);
    }
}
