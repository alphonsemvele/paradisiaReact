<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * La colonne héritée notifications.type est un enum (project/publication/
     * payment) qui n'accepte pas « message ». On l'élargit en VARCHAR pour
     * accepter les nouveaux types (messagerie, etc.).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `notifications` MODIFY `type` VARCHAR(40) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `notifications` MODIFY `type` ENUM('project','publication','payment') NOT NULL");
    }
};
