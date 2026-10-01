<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remplace toute image de publication pointant vers loremflickr (désormais
     * en HTTP 401 → cassée) par de vraies photos de produits (hébergées sur le
     * domaine), avec repli Unsplash. Ciblage sur « loremflickr » : pas de
     * backslash/underscore, donc indépendant du mode SQL (contrairement au
     * LIKE 'SEEDJUS\_%' précédent qui ne matchait rien).
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Photos de produits disponibles (fiables, même domaine).
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

        $ids = DB::table('publications')
            ->where('img_1', 'like', '%loremflickr%')
            ->orWhere('img_2', 'like', '%loremflickr%')
            ->orWhere('img_3', 'like', '%loremflickr%')
            ->orWhere('img_4', 'like', '%loremflickr%')
            ->orWhere('img_5', 'like', '%loremflickr%')
            ->orderBy('id')->pluck('id')->all();

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
        // Rien : les anciennes URL loremflickr étaient cassées.
    }
};
