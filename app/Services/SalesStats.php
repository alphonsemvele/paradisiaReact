<?php

namespace App\Services;

use App\Models\Product;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class SalesStats
{
    /**
     * Quantités vendues par produit SIMPLE.
     *
     * Les ventes de produits composés sont décomposées : pour chaque composant,
     * on ajoute (quantité vendue du composé × quantité présente dans la composition).
     * Ex : 3 cartons de 12 bouteilles vendus  ->  36 bouteilles.
     *
     * @return array<int, array{id:int, name:string, image:?string, qty:int}>
     */
    public static function simpleProductQuantities(?int $limit = null): array
    {
        // Quantités brutes vendues par produit (ventes validées uniquement)
        $sold = SaleItem::query()
            ->whereHas('sale', fn ($q) => $q->where('status', 'Success'))
            ->select('id_product', DB::raw('SUM(quantity) as qty'))
            ->groupBy('id_product')
            ->pluck('qty', 'id_product');

        if ($sold->isEmpty()) {
            return [];
        }

        // Tous les produits + leur composition (pour décomposer les composés)
        $products = Product::with('components')->get()->keyBy('id');

        $totals = []; // [id_produit_simple => quantité]

        foreach ($sold as $productId => $qty) {
            $product = $products->get($productId);
            if (! $product) {
                continue;
            }

            if ($product->type === 'compose') {
                foreach ($product->components as $component) {
                    $simpleId = $component->id_component_product;
                    $totals[$simpleId] = ($totals[$simpleId] ?? 0) + ($qty * $component->quantity);
                }
            } else {
                $totals[$productId] = ($totals[$productId] ?? 0) + $qty;
            }
        }

        arsort($totals);

        if ($limit) {
            $totals = array_slice($totals, 0, $limit, true);
        }

        $result = [];
        foreach ($totals as $id => $qty) {
            $product = $products->get($id);
            if (! $product) {
                continue;
            }
            $result[] = [
                'id'    => (int) $id,
                'name'  => $product->name,
                'image' => $product->img_1 ? asset($product->img_1) : null,
                'qty'   => (int) $qty,
            ];
        }

        return $result;
    }
}
