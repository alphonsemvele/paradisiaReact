<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Les fans ont désormais eux aussi un dossard (001..300), par zone, en plus
     * des participants (001..160). Les deux séries sont indépendantes : un
     * participant et un fan peuvent porter le même numéro dans la même zone.
     *
     * L'unicité passe donc de (zone, dossard) à (zone, type, dossard).
     */
    public function up(): void
    {
        Schema::table('festy_tickets', function (Blueprint $table) {
            $table->dropUnique(['zone', 'dossard']);
            $table->unique(['zone', 'type', 'dossard']);
        });
    }

    public function down(): void
    {
        Schema::table('festy_tickets', function (Blueprint $table) {
            $table->dropUnique(['zone', 'type', 'dossard']);
            $table->unique(['zone', 'dossard']);
        });
    }
};
