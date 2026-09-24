<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration native du squelette Laravel, réduite à `sessions`.
 *
 * Paradisia définit son propre `users` (ref, id_father, role, referral_code…)
 * dans 2026_01_17_110636, et `password_reset_tokens` dans 2026_08_11_120000.
 * Comme ce fichier s'exécute en premier, il créait la version générique de ces
 * deux tables, et les migrations applicatives échouaient ensuite sur un
 * « table already exists » — ce qui rendait toute base neuve impossible à
 * monter, et bloquait l'intégralité de la suite de tests.
 *
 * Seule `sessions` reste ici : aucune autre migration ne la définit.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sessions')) {
            return;
        }

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
