<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Destinataires des notifications admin par e-mail (paiements, inscriptions,
     * tickets à valider…). Gérés depuis l'administration.
     */
    public function up(): void
    {
        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->string('email', 190)->unique();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        DB::table('notification_recipients')->insert([
            'email' => 'alphonsemveleloic@gmail.com',
            'actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_recipients');
    }
};
