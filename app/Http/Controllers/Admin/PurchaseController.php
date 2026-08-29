<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Purchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/** Achats : on répertorie tout, y compris les achats pour la production. */
class PurchaseController extends Controller
{
    public function index(): Response
    {
        $achats = Purchase::withCount('items')->orderByDesc('date_achat')->orderByDesc('id')->limit(100)->get()
            ->map(fn (Purchase $p) => [
                'id' => $p->id,
                'fournisseur' => $p->fournisseur,
                'date' => $p->date_achat?->isoFormat('D MMM YYYY'),
                'total' => (float) $p->total,
                'pour_production' => $p->pour_production,
                'nb_lignes' => $p->items_count,
                'note' => $p->note,
            ]);

        return Inertia::render('admin/achats/index', [
            'achats' => $achats,
            'materiels' => Material::where('actif', true)->orderBy('nom')->get(['id', 'nom', 'unite', 'type']),
            'stats' => [
                'total_mois' => (float) Purchase::whereMonth('date_achat', now()->month)->whereYear('date_achat', now()->year)->sum('total'),
                'nb' => Purchase::count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fournisseur' => ['nullable', 'string', 'max:160'],
            'date_achat' => ['required', 'date'],
            'pour_production' => ['boolean'],
            'note' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_id' => ['required', 'integer', 'exists:materials,id'],
            'items.*.quantite' => ['required', 'numeric', 'min:0.01'],
            'items.*.cout_unitaire' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($data) {
            $total = 0;
            $achat = Purchase::create([
                'fournisseur' => $data['fournisseur'] ?? null,
                'date_achat' => $data['date_achat'],
                'pour_production' => (bool) ($data['pour_production'] ?? false),
                'note' => $data['note'] ?? null,
                'id_user' => auth()->id(),
                'total' => 0,
            ]);

            foreach ($data['items'] as $it) {
                $q = (float) $it['quantite'];
                $cu = (float) ($it['cout_unitaire'] ?? 0);
                $st = $q * $cu;
                $total += $st;

                $achat->items()->create([
                    'material_id' => $it['material_id'],
                    'quantite' => $q,
                    'cout_unitaire' => $cu,
                    'sous_total' => $st,
                ]);

                // Entrée en stock.
                Material::mouvement((int) $it['material_id'], 'achat', $q, [
                    'motif' => 'Achat'.($achat->fournisseur ? ' — '.$achat->fournisseur : ''),
                    'ref_type' => 'purchase',
                    'ref_id' => $achat->id,
                ]);
            }

            $achat->update(['total' => $total]);
        });

        return back()->with('success', 'Achat enregistré et stock mis à jour.');
    }

    public function show(Purchase $purchase): Response
    {
        $purchase->load('items.material');

        return Inertia::render('admin/achats/show', [
            'achat' => [
                'id' => $purchase->id,
                'fournisseur' => $purchase->fournisseur,
                'date' => $purchase->date_achat?->isoFormat('D MMMM YYYY'),
                'total' => (float) $purchase->total,
                'pour_production' => $purchase->pour_production,
                'note' => $purchase->note,
                'items' => $purchase->items->map(fn ($i) => [
                    'materiel' => $i->material?->nom ?? '—',
                    'unite' => $i->material?->unite ?? '',
                    'quantite' => (float) $i->quantite,
                    'cout_unitaire' => (float) $i->cout_unitaire,
                    'sous_total' => (float) $i->sous_total,
                ]),
            ],
        ]);
    }

    public function destroy(Purchase $purchase): RedirectResponse
    {
        // On annule les entrées de stock de cet achat, puis on supprime.
        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $it) {
                Material::mouvement($it->material_id, 'ajustement', -1 * (float) $it->quantite, [
                    'motif' => 'Annulation achat #'.$purchase->id,
                ]);
            }
            $purchase->delete();
        });

        return back()->with('success', 'Achat supprimé et stock corrigé.');
    }
}
