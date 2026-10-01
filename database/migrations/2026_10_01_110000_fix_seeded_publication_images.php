<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Les publications semées pointaient vers loremflickr (désormais en 401) :
     * images cassées. On les remplace par de VRAIES photos de produits
     * (hébergées sur le domaine, donc fiables), avec repli sur une image
     * générique si aucun produit n'a de photo.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Photos de produits disponibles (img_1 + img_2).
        $imgs = [];
        foreach (DB::table('products')->where('status', 'Success')->get(['img_1', 'img_2']) as $p) {
            if (! empty($p->img_1)) {
                $imgs[] = $p->img_1;
            }
            if (! empty($p->img_2)) {
                $imgs[] = $p->img_2;
            }
        }

        if (empty($imgs)) {
            $imgs = ['https://images.unsplash.com/photo-1619566636858-adf3ef46400b?auto=format&fit=crop&w=1000&q=70'];
        }

        $n = count($imgs);
        $ids = DB::table('publications')->where('ref', 'like', 'SEEDJUS\_%')->orderBy('id')->pluck('id')->all();

        $k = 0;
        foreach ($ids as $id) {
            DB::table('publications')->where('id', $id)->update([
                'img_1' => $imgs[$k % $n],
                'img_2' => $n > 1 ? $imgs[($k + 1) % $n] : null,
                'img_3' => null,
                'img_4' => null,
                'img_5' => null,
            ]);
            $k += 2;
        }
    }

    public function down(): void
    {
        // Pas de retour en arrière utile (les anciennes URL étaient cassées).
    }
};
