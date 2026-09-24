<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Doublon historique de la table `jobs` de Laravel.
 *
 * Blueprint a généré cette migration alors que le squelette Laravel crée déjà
 * `jobs` dans 0001_01_01_000002, qui s'exécute avant. Sur une base neuve — la
 * suite de tests, par exemple — elle échouait donc sur « table jobs already
 * exists » et bloquait TOUS les tests.
 *
 * Le fichier est conservé plutôt que supprimé : il figure déjà dans l'historique
 * des bases existantes. Il ne fait simplement plus rien.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('jobs')) {
            return;
        }

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
    }

    public function down(): void
    {
        // Ne supprime rien : la table appartient à la migration native de
        // Laravel, c'est à elle de la défaire.
    }
};
