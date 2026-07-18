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
        DB::statement("ALTER TABLE payments MODIFY type_paiement ENUM('Contact','Mobile','Bank','Bonus','Wallet') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE payments SET type_paiement = 'Mobile' WHERE type_paiement = 'Wallet'");
        DB::statement("ALTER TABLE payments MODIFY type_paiement ENUM('Contact','Mobile','Bank','Bonus') NOT NULL");
    }
};
