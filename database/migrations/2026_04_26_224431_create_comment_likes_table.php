<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_comment')->constrained('comments')->onDelete('cascade');
            $table->ipAddress('ip_address')->nullable();
            $table->string('status')->default('Success');
            $table->timestamps();

            $table->unique(['id_user', 'id_comment']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_likes');
    }
};