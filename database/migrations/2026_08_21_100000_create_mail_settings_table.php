<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('actif')->default(false);
            $table->string('mailer', 20)->default('smtp');   // smtp | sendmail
            $table->string('host')->nullable();
            $table->unsignedInteger('port')->default(465);
            $table->string('username')->nullable();
            $table->text('password')->nullable();            // chiffré
            $table->string('encryption', 10)->nullable();    // ssl | tls | null
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_settings');
    }
};
