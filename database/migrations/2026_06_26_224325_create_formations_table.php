<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Définition remplacée le lendemain par
        // 2026_06_27_125332_create_formations_catalog_table, plus complète.
        // Fichier conservé car déjà présent dans l'historique des bases
        // existantes ; il ne recrée plus la table.
        if (Schema::hasTable('formations')) {
            return;
        }

        Schema::create('formations', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 120);
            $table->string('prenom', 120);
            $table->string('telephone', 30)->nullable();
            $table->enum('type', ['acceleree', 'normale']);
            $table->enum('statut', ['en_attente', 'confirme', 'annule'])->default('en_attente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};
