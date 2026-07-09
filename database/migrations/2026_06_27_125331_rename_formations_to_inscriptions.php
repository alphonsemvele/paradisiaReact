<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * L'ancienne table "formations" contenait en réalité les inscriptions
     * (nom, prenom, telephone, type). On la renomme en "inscriptions" pour
     * libérer le nom "formations" au profit du catalogue géré par l'admin.
     */
    public function up(): void
    {
        Schema::rename('formations', 'inscriptions');
    }

    public function down(): void
    {
        Schema::rename('inscriptions', 'formations');
    }
};
