<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Suivi des vues des publications.
     *
     * - id_user devient nullable : un visiteur non connecté compte aussi.
     * - ip_address passe en varchar(45) (IPv6 max) pour être indexable ;
     *   c'était un longtext, impossible à indexer sans préfixe.
     * - index (id_publication, created_at) : les statistiques par jour et le
     *   comptage restent instantanés même avec beaucoup de vues.
     */
    public function up(): void
    {
        // Tronque les éventuelles valeurs trop longues avant de réduire le type
        DB::statement('UPDATE views SET ip_address = LEFT(ip_address, 45) WHERE CHAR_LENGTH(ip_address) > 45');

        DB::statement('ALTER TABLE views MODIFY id_user BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE views MODIFY ip_address VARCHAR(45) NULL');

        Schema::table('views', function (Blueprint $table) {
            $table->index(['id_publication', 'created_at'], 'views_publication_date_idx');
            $table->index(['id_publication', 'ip_address'], 'views_publication_ip_idx');
        });
    }

    public function down(): void
    {
        Schema::table('views', function (Blueprint $table) {
            $table->dropIndex('views_publication_date_idx');
            $table->dropIndex('views_publication_ip_idx');
        });

        DB::statement('ALTER TABLE views MODIFY ip_address LONGTEXT NULL');
        DB::statement('ALTER TABLE views MODIFY id_user BIGINT UNSIGNED NOT NULL');
    }
};
