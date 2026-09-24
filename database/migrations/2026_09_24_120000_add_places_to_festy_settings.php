<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nombre de places « participant » par équipe (20 par défaut). Les tickets
     * « fan » restent illimités. Configurable depuis l'administration.
     */
    public function up(): void
    {
        Schema::table('festy_settings', function (Blueprint $table) {
            $table->unsignedInteger('places_participant_equipe')->default(20)->after('promo_fin');
        });

        DB::table('festy_settings')->update(['places_participant_equipe' => 20]);
    }

    public function down(): void
    {
        Schema::table('festy_settings', function (Blueprint $table) {
            $table->dropColumn('places_participant_equipe');
        });
    }
};
