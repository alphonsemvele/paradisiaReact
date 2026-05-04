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

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users', 'user');
            $table->string('name', 500);
            $table->longText('description')->nullable();
            $table->string('ref', 150)->nullable();
            $table->string('website_url', 500)->nullable();
            $table->enum('status', ["pending","Success","failed","waiting"]);
            $table->string('logo_125_125', 255);
            $table->string('img_banniere_1202_425', 100)->nullable();
            $table->string('facebook', 255)->nullable();
            $table->string('twitter', 255)->nullable();
            $table->string('instagram', 255)->nullable();
            $table->string('youtube', 255)->nullable();
            $table->string('sigle', 20);
            $table->string('bio', 255)->nullable();
            $table->longText('video')->nullable();
            $table->longText('whatsapp')->nullable();
            $table->string('country', 255);
            $table->integer('country_code');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
