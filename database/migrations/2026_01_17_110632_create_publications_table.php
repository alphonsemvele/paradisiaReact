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

        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 255);
            $table->string('title', 255)->nullable();
            $table->text('text')->nullable();
            $table->foreignId('id_user')->constrained('users', 'user');
            $table->foreignId('id_project')->nullable()->constrained('projects', 'project');
            $table->foreignId('id_page')->nullable()->constrained('pages', 'page');
            $table->enum('status', ["Success","pending","failed","waiting","deleted"]);
            $table->integer('nbr_vews')->nullable();
            $table->string('image', 500)->nullable();
            $table->string('video', 255)->nullable();
            $table->string('audio', 500)->nullable();
            $table->enum('type', ["publicity","publication"]);
            $table->enum('typeServicePublicity', ["sms","email"])->nullable();
            $table->string('country', 255)->nullable();
            $table->foreignId('id_country')->nullable();
            $table->string('ip_address', 255)->nullable();
            $table->string('attachment', 1000)->nullable();
            $table->integer('amount')->nullable();
            $table->foreignId('user_id');
            $table->foreignId('project_id');
            $table->foreignId('page_id');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
