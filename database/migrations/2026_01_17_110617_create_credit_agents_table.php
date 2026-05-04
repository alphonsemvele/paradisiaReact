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

        Schema::create('credit_agents', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 255);
            $table->foreignId('id_user')->constrained('users', 'user');
            $table->bigInteger('id_admin');
            $table->foreign('id_admin')->references('admin')->on('admins');
            $table->enum('status', ["pending","Success","failed"]);
            $table->integer('amount');
            $table->string('currency', 255);
            $table->longText('commentaire')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_agents');
    }
};
