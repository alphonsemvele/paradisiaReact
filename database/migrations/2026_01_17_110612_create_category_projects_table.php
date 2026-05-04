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

        Schema::create('category_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('icone', 255)->nullable();
            $table->enum('status', ["pending","Success","failed","waiting"]);
            $table->string('color', 255)->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_projects');
    }
};
