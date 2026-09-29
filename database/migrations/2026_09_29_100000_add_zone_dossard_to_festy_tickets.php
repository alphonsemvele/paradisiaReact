<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Zone de participation (Yaoundé / Douala) et numéro de dossard (1..160)
     * pour les tickets Participant. Un dossard est unique par zone ; il est
     * libéré (remis à null) si le ticket est annulé / échoué.
     */
    public function up(): void
    {
        Schema::table('festy_tickets', function (Blueprint $table) {
            $table->string('zone', 20)->nullable()->after('festy_team_id');
            $table->unsignedSmallInteger('dossard')->nullable()->after('zone');

            // Deux participants ne peuvent pas avoir le même dossard dans une
            // même zone. Les NULL (fans, tickets libérés) ne sont pas contraints.
            $table->unique(['zone', 'dossard']);
        });
    }

    public function down(): void
    {
        Schema::table('festy_tickets', function (Blueprint $table) {
            $table->dropUnique(['zone', 'dossard']);
            $table->dropColumn(['zone', 'dossard']);
        });
    }
};
