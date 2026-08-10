<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Le téléphone provient désormais du compte et peut être absent :
     * on le rend nullable pour ne pas bloquer l'inscription.
     */
    public function up(): void
    {
        Schema::table('festy_registrations', function (Blueprint $table) {
            $table->string('telephone', 40)->nullable()->change();
            $table->string('nom', 160)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('festy_registrations', function (Blueprint $table) {
            $table->string('telephone', 40)->nullable(false)->change();
            $table->string('nom', 160)->nullable(false)->change();
        });
    }
};
