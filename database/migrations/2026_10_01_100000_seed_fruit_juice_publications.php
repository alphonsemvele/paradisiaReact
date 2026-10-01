<?php

use App\Models\Publication;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Sème des publications « le saviez-vous » sur les jus & fruits pour animer
     * le fil (images en carrousel façon Facebook). Les images sont des URL
     * externes par mots-clés (mediaUrl laisse passer les URL absolues).
     *
     * Prod/MySQL uniquement : la base importée possède img_1..img_5 ; SQLite
     * (tests) ne les a pas, on saute donc proprement.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Déjà semé ? on ne double pas.
        if (DB::table('publications')->where('ref', 'like', 'SEEDJUS\_%')->exists()) {
            return;
        }

        // Compte auteur : un admin de préférence, sinon le premier utilisateur.
        $userId = DB::table('users')->whereIn('role', ['admin', 'super-admin'])->orderBy('id')->value('id')
            ?? DB::table('users')->orderBy('id')->value('id');

        if (! $userId) {
            return; // aucun utilisateur : rien à faire
        }

        $img = fn (string $kw, int $lock) => "https://loremflickr.com/1000/750/{$kw}?lock={$lock}";

        $posts = [
            ['🍍 Le saviez-vous ? L\'ananas renferme de la bromélaïne, une enzyme qui facilite la digestion. Un bon verre de jus d\'ananas frais après le repas, et le tour est joué ! #Paradisia #Ananas', ['pineapple,juice|11', 'pineapple,fruit|12']],
            ['🍊 Vitamine C à volonté ! Un seul verre de jus d\'orange fraîchement pressé couvre presque tous tes besoins quotidiens. Le réveil en douceur, version naturelle. ☀️', ['orange,juice|21']],
            ['🥭 La mangue, reine des tropiques 👑 Riche en vitamine A, elle prend soin de ta peau et de tes yeux. En jus, c\'est un pur délice crémeux.', ['mango,fruit|31', 'mango,juice|32']],
            ['🍉 95% d\'eau, 100% rafraîchissant. Le jus de pastèque, c\'est l\'hydratation plaisir sous le soleil. Qui en reprend ? 🙋', ['watermelon,juice|41']],
            ['🫚 Un petit shot de gingembre pour réveiller les défenses ! Piquant, tonique, efficace. Tu oses ? 🔥', ['ginger,drink|51']],
            ['🍋 Astuce du matin : eau tiède + citron, le petit geste tout simple qui met ton métabolisme en route. 🍋✨', ['lemon,water|61']],
            ['😋 Le fruit de la passion, cette explosion acidulée… parfait pour parfumer tes jus et tes cocktails sans alcool.', ['passion,fruit|71', 'passionfruit,juice|72']],
            ['🍌 Une banane = énergie durable + potassium pour tes muscles. En smoothie avec un peu de lait, c\'est le carburant des champions. 💪', ['banana,smoothie|81']],
            ['🧡 La papaye, douce et fondante, aide la digestion grâce à la papaïne. Un jus onctueux et tout doux pour le ventre.', ['papaya,fruit|91']],
            ['🥑 Oui, l\'avocat est un fruit ! Riche en bonnes graisses, il rend tes smoothies ultra crémeux. Essaie avec banane + un filet de miel. 🍯', ['avocado|101']],
            ['💧 Astuce Paradisia : remplace les sodas par un jus 100% naturel. Même plaisir sucré, zéro additif. Ton corps dit merci 🙏', ['fruit,juice,glass|111', 'fresh,juice|112']],
            ['🌈 Pourquoi choisir ? Mélange ananas + mangue + fruit de la passion pour un cocktail tropical qui met tout le monde d\'accord. 🍍🥭', ['tropical,juice|121', 'mango,pineapple|122', 'cocktail,fruit|123']],
        ];

        foreach ($posts as $index => [$text, $images]) {
            $data = [
                'ref' => 'SEEDJUS_'.Str::random(10),
                'text' => $text,
                'id_user' => $userId,
                'status' => 'Success',
                'type' => 'publication',
                'nbr_vews' => random_int(20, 320),
            ];

            foreach ($images as $i => $spec) {
                [$kw, $lock] = explode('|', $spec);
                $data['img_'.($i + 1)] = $img($kw, (int) $lock);
            }

            $pub = Publication::create($data);

            // created_at n'est pas « fillable » : on le règle ensuite pour
            // échelonner les dates (les plus récentes en premier).
            $date = now()->subHours($index * 6 + random_int(0, 5));
            DB::table('publications')->where('id', $pub->id)
                ->update(['created_at' => $date, 'updated_at' => $date]);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('publications')->where('ref', 'like', 'SEEDJUS\_%')->delete();
    }
};
