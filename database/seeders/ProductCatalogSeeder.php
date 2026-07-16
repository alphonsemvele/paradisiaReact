<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductComponent;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Catalogue des jus PARADISIA : 3 bouteilles (produits simples) et
 * 7 cartons de 12 bouteilles (produits composés, cf. product_components).
 *
 * Idempotent : rejouable à chaque déploiement sans doublon (clé name + type).
 * Images : public/images/products/, servies telles quelles par le docroot.
 */
class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(['name' => 'jus de fruit']);

        $owner = User::where('email', 'paradisiainvestment@gmail.com')->value('id')
            ?? User::orderBy('id')->value('id');

        if (! $owner) {
            $this->command?->warn('Aucun utilisateur en base : catalogue non créé.');

            return;
        }

        $defaults = [
            'id_category' => $category->id,
            'id_user' => $owner,
            'status' => 'Success',
        ];

        // ── Produits simples (la bouteille) ────────────────────────────
        $simples = [];
        foreach ([
            'Ananas simple' => 'images/products/ananas.jpeg',
            'Ananas citron' => 'images/products/citron.jpeg',
            'Ananas gingembre' => 'images/products/gingembre.jpeg',
        ] as $name => $image) {
            $simples[$name] = Product::updateOrCreate(
                ['name' => $name, 'type' => 'simple'],
                $defaults + [
                    'price' => 1000,
                    'description' => '1 bouteille',
                    'img_1' => $image,
                ]
            );
        }

        // ── Produits composés (le carton de 12) ────────────────────────
        // nom => [prix, description, [produit simple => quantité]]
        $cartons = [
            'Ananas simple (carton)' => [8500, '1 carton (12 bouteilles)', ['Ananas simple' => 12]],
            'Ananas citron (carton)' => [8500, '1 carton (12 bouteilles)', ['Ananas citron' => 12]],
            'Ananas gingembre (carton)' => [8500, '1 carton (12 bouteilles)', ['Ananas gingembre' => 12]],
            'Ananas simple, citron (carton)' => [9000, '1 carton (12 bouteilles : 6 citron et 6 ananas simple)', ['Ananas citron' => 6, 'Ananas simple' => 6]],
            'Ananas simple, gingembre (carton)' => [9000, '1 carton (12 bouteilles : 6 gingembre et 6 ananas simple)', ['Ananas gingembre' => 6, 'Ananas simple' => 6]],
            'Ananas citron, gingembre (carton)' => [9000, '1 carton (12 bouteilles : 6 citron et 6 gingembre)', ['Ananas citron' => 6, 'Ananas gingembre' => 6]],
            'Ananas simple, citron, gingembre (carton)' => [9500, '1 carton (12 bouteilles : 4 citron, 4 gingembre et 4 ananas simple)', ['Ananas citron' => 4, 'Ananas gingembre' => 4, 'Ananas simple' => 4]],
        ];

        foreach ($cartons as $name => [$price, $description, $composition]) {
            $carton = Product::updateOrCreate(
                ['name' => $name, 'type' => 'compose'],
                $defaults + [
                    'price' => $price,
                    'description' => $description,
                    'img_1' => 'images/products/compose.jpeg',
                ]
            );

            foreach ($composition as $simpleName => $quantity) {
                ProductComponent::updateOrCreate(
                    [
                        'id_composite_product' => $carton->id,
                        'id_component_product' => $simples[$simpleName]->id,
                    ],
                    ['quantity' => $quantity]
                );
            }

            // Retire d'éventuels composants qui ne sont plus dans la recette
            ProductComponent::where('id_composite_product', $carton->id)
                ->whereNotIn('id_component_product', collect($composition)->keys()->map(fn ($n) => $simples[$n]->id))
                ->delete();
        }
    }
}
