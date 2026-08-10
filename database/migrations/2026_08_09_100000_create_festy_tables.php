<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PARADISIA FESTY : événement à équipes. Les participants s'inscrivent et
     * rejoignent une équipe, puis son groupe WhatsApp. Tout est configurable
     * depuis l'administration (réglages, équipes, lien du groupe).
     */
    public function up(): void
    {
        // Réglages généraux (une seule ligne).
        Schema::create('festy_settings', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 120)->default('PARADISIA FESTY');
            $table->string('sous_titre', 255)->nullable();
            $table->string('date_label', 120)->nullable();   // « Décembre 2026 »
            $table->string('prix', 120)->nullable();         // « 300 000 FCFA »
            $table->text('description')->nullable();
            $table->boolean('inscriptions_ouvertes')->default(true);
            $table->timestamps();
        });

        // Équipes.
        Schema::create('festy_teams', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 80);
            $table->string('trait', 120)->nullable();        // « Énergie & Force »
            $table->string('couleur', 20)->default('#F5B301'); // couleur d'accent
            $table->string('emoji', 16)->nullable();
            $table->string('image', 255)->nullable();
            $table->string('whatsapp_group', 500)->nullable(); // lien du groupe
            $table->boolean('actif')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        // Inscriptions.
        Schema::create('festy_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('festy_team_id')->constrained('festy_teams')->cascadeOnDelete();
            $table->string('nom', 160);
            $table->string('telephone', 40);
            $table->string('email', 180)->nullable();
            $table->string('ville', 120)->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            // Une personne ne s'inscrit qu'une fois (par téléphone).
            $table->unique('telephone');
            $table->index('festy_team_id');
        });

        // Données initiales (les 5 équipes de l'affiche).
        DB::table('festy_settings')->insert([
            'titre' => 'PARADISIA FESTY',
            'sous_titre' => "Le rendez-vous qui célèbre la jeunesse, la culture et nos richesses !",
            'date_label' => 'Décembre 2026',
            'prix' => '300 000 FCFA',
            'description' => "Choisis ton équipe et rejoins l'aventure : jeux, défis, duels, challenges, musique, ambiance et lots à gagner. Une seule mission : s'amuser, se dépasser et gagner ensemble !",
            'inscriptions_ouvertes' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $equipes = [
            ['Ananas', 'Énergie & Force', '#F5B301', '🍍'],
            ['Citron', 'Fraîcheur & Vitesse', '#8BC34A', '🍋'],
            ['Gingembre', 'Endurance & Résistance', '#B8860B', '🫚'],
            ['Orange', 'Vitalité & Positivité', '#FF8C00', '🍊'],
            ['Papaye', 'Créativité & Stratégie', '#E8792B', '🥭'],
        ];

        foreach ($equipes as $i => [$nom, $trait, $couleur, $emoji]) {
            DB::table('festy_teams')->insert([
                'nom' => $nom,
                'trait' => $trait,
                'couleur' => $couleur,
                'emoji' => $emoji,
                'actif' => true,
                'position' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('festy_registrations');
        Schema::dropIfExists('festy_teams');
        Schema::dropIfExists('festy_settings');
    }
};
