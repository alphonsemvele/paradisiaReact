<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permet de rendre l'e-mail facultatif à l'inscription à un événement
     * (ex. meeting présentiel où l'on contacte les gens par WhatsApp).
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('email_optionnel')->default(false)->after('collecte_nom');
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->string('email', 180)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('email_optionnel');
        });

        Schema::table('event_registrations', function (Blueprint $table) {
            $table->string('email', 180)->nullable(false)->change();
        });
    }
};
