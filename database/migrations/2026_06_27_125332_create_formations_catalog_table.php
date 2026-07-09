<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catalogue des formations créées par l'administrateur.
     */
    public function up(): void
    {
        Schema::create('formations', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 180);
            $table->text('description')->nullable();
            $table->decimal('prix', 12, 2)->default(0);
            $table->string('duree', 100)->nullable();   // ex : "3 mois", "40 heures"
            $table->string('session', 120)->nullable(); // ex : "Session de Janvier 2026"
            $table->string('image')->nullable();         // chemin de l'image
            $table->string('document')->nullable();      // chemin du document (PDF...)
            $table->enum('statut', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};
