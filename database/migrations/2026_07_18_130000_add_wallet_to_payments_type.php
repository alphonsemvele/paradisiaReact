<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Les paiements passent désormais par les portefeuilles Malapay : ni
     * « Mobile » (opérateur mobile money) ni « Bank » ne décrivent ce canal.
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

        DB::statement("ALTER TABLE payments MODIFY type_paiement ENUM('Contact','Mobile','Bank','Bonus','Wallet') NOT NULL");
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

        DB::statement("UPDATE payments SET type_paiement = 'Mobile' WHERE type_paiement = 'Wallet'");
        DB::statement("ALTER TABLE payments MODIFY type_paiement ENUM('Contact','Mobile','Bank','Bonus') NOT NULL");
    }
};
