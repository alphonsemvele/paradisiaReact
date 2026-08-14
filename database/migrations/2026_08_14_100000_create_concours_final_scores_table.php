<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Points « réponses » saisis à la main pour la dernière phase du jeu
     * concours (5 pts par réponse juste, corrigées manuellement). Les likes et
     * commentaires sont calculés automatiquement en direct.
     */
    public function up(): void
    {
        Schema::create('concours_final_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user')->unique();
            $table->unsignedTinyInteger('reponses_justes')->default(0); // 0..10
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concours_final_scores');
    }
};
