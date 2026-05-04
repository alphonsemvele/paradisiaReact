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

        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable();
            $table->enum('type', ["invest","don","studyFees","pub"]);
            $table->integer('value');
            $table->enum('status', ["pending","Success","failed","waiting"]);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
