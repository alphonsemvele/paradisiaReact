<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Les comptes déjà existants sont considérés comme vérifiés : on ne bloque
     * QUE les nouvelles inscriptions (qui devront confirmer par e-mail).
     */
    public function up(): void
    {
        DB::table('users')->whereNull('email_verified_at')->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Irréversible volontairement (on ne « dé-vérifie » pas les comptes).
    }
};
