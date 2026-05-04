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

        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_user')->nullable();
            $table->string('ip_address', 15)->nullable();
            $table->foreignId('id_publication')->nullable()->constrained('publications', 'publication');
            $table->foreignId('id_project')->nullable()->constrained('projects', 'project');
            $table->foreignId('id_page')->nullable()->constrained('pages', 'page');
            $table->enum('status', ["Success","pending","failed","waiting"]);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
