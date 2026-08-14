<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Corrigé du quiz de la dernière phase (10 questions + bonnes réponses). */
    public function up(): void
    {
        Schema::create('concours_final_settings', function (Blueprint $table) {
            $table->id();
            $table->json('corrige')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concours_final_settings');
    }
};
