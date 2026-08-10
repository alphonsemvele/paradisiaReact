<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('festy_registrations', function (Blueprint $table) {
            $table->string('prenom', 120)->nullable()->after('nom');
            $table->string('quartier', 160)->nullable()->after('ville');
        });
    }

    public function down(): void
    {
        Schema::table('festy_registrations', function (Blueprint $table) {
            $table->dropColumn(['prenom', 'quartier']);
        });
    }
};
