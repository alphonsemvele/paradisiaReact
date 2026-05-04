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

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ["project","publication","payment"]);
            $table->text('body');
            $table->enum('status', ["Success","pending","failed","waiting"]);
            $table->foreignId('id_project')->nullable()->constrained('projects', 'project');
            $table->foreignId('id_publication')->nullable()->constrained('publications', 'publication');
            $table->bigInteger('id_user');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
