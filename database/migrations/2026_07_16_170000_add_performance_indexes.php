<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index manquants sur les tables du fil d'actualité. La table comments
     * n'avait AUCUN index hors clé primaire : chaque affichage de l'accueil
     * scannait la table entière. publications n'avait pas d'index couvrant
     * son filtre principal (status + created_at).
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            if (! Schema::hasIndex('comments', 'comments_id_publication_index')) {
                $table->index('id_publication');
            }
            if (! Schema::hasIndex('comments', 'comments_parent_id_index')) {
                $table->index('parent_id');
            }
        });

        Schema::table('publications', function (Blueprint $table) {
            if (! Schema::hasIndex('publications', 'publications_status_created_at_index')) {
                $table->index(['status', 'created_at']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_id_publication_index');
            $table->dropIndex('comments_parent_id_index');
        });

        Schema::table('publications', function (Blueprint $table) {
            $table->dropIndex('publications_status_created_at_index');
        });
    }
};
