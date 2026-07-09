<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mode de la formation : en présentiel ou en ligne.
     */
    public function up(): void
    {
        Schema::table('formations', function (Blueprint $table) {
            $table->enum('mode', ['presentiel', 'en_ligne'])
                ->default('presentiel')
                ->after('session');
        });
    }

    public function down(): void
    {
        Schema::table('formations', function (Blueprint $table) {
            $table->dropColumn('mode');
        });
    }
};
