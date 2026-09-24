<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Deux défauts hérités de la base importée :
     *
     * - img_1 était NOT NULL sans valeur par défaut : publier un texte sans
     *   photo échouait (SQLSTATE 1364) dès que MySQL est en mode strict.
     * - nbr_vews était NULL par défaut : `nbr_vews + 1` sur NULL vaut NULL,
     *   le compteur de vues serait donc resté vide.
     */
    public function up(): void
    {
        // Ces instructions sont du SQL MySQL/MariaDB (MODIFY COLUMN, ENUM,
        // LEFT, CHAR_LENGTH). Sous SQLite — la suite de tests — elles
        // échouent et bloquent toutes les migrations suivantes. Le type y
        // reste un varchar sans contrainte : le comportement applicatif est
        // identique, seule la contrainte SGBD manque.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE publications MODIFY img_1 TEXT NULL');
        DB::statement('UPDATE publications SET nbr_vews = 0 WHERE nbr_vews IS NULL');
        DB::statement('ALTER TABLE publications MODIFY nbr_vews INT UNSIGNED NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        // Ces instructions sont du SQL MySQL/MariaDB (MODIFY COLUMN, ENUM,
        // LEFT, CHAR_LENGTH). Sous SQLite — la suite de tests — elles
        // échouent et bloquent toutes les migrations suivantes. Le type y
        // reste un varchar sans contrainte : le comportement applicatif est
        // identique, seule la contrainte SGBD manque.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE publications MODIFY nbr_vews INT(10) NULL');
        // img_1 est laissée nullable : la remettre NOT NULL casserait les
        // publications sans image créées entre-temps.
    }
};
