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

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100);
            $table->string('location', 100);
            $table->string('name', 100);
            $table->integer('id_users_pack_pub')->nullable();
            $table->integer('id_publication')->nullable();
            $table->integer('id_project')->nullable();
            $table->integer('id_fundraising')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
