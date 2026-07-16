<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - prix_inscription : les frais d'inscription sont dissociés du prix
     *   de la formation elle-même.
     * - formation_images : galerie multi-images d'une formation ; l'ancienne
     *   colonne image reste la couverture.
     */
    public function up(): void
    {
        Schema::table('formations', function (Blueprint $table) {
            if (! Schema::hasColumn('formations', 'prix_inscription')) {
                $table->decimal('prix_inscription', 12, 2)->default(0)->after('prix');
            }
        });

        Schema::create('formation_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->constrained('formations')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formation_images');

        Schema::table('formations', function (Blueprint $table) {
            if (Schema::hasColumn('formations', 'prix_inscription')) {
                $table->dropColumn('prix_inscription');
            }
        });
    }
};
