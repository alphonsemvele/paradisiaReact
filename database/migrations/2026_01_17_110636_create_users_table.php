<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 255)->nullable();
            $table->foreignId('id_father')->nullable()->constrained('users', 'father');
            $table->string('name', 191);
            $table->string('last_name', 255)->default(' ');
            $table->string('email', 191)->unique();
            $table->string('password', 255);
            $table->string('remember_token', 100)->nullable();
            $table->enum('role', ["user","agent","admin"]);
            $table->tinyInteger('valid')->default(0);
            $table->tinyInteger('confirmed')->default(0);
            $table->string('confirmation_code', 191)->nullable();
            $table->integer('default_currency')->nullable();
            $table->string('referral_code', 255)->nullable();
            $table->enum('status', ["pending","Success","failed","waiting"]);
            $table->string('country', 100)->nullable();
            $table->string('phone', 100)->nullable();
            $table->string('ville', 100)->nullable();
            $table->dateTime('last_connect')->nullable();
            $table->string('cover_img', 200)->nullable();
            $table->dateTime('last_active')->nullable();
            $table->longText('photo')->nullable();
            $table->string('description', 255)->nullable();
            $table->date('birth')->nullable();
            $table->integer('country_code')->nullable();
            $table->string('recoveryPass_code', 255)->nullable();
            $table->enum('sexe', ["H","F"]);
            $table->bigInteger('id_country');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
