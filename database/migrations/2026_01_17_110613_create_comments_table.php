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

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users', 'user');
            $table->foreignId('id_publication')->nullable()->constrained('publications', 'publication');
            $table->foreignId('id_project')->nullable()->constrained('projects', 'project');
            $table->foreignId('id_page')->nullable()->constrained('pages', 'page');
            $table->longText('body');
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
        Schema::dropIfExists('comments');
    }
};
