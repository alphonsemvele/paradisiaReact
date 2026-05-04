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

        Schema::create('pack_pubs', function (Blueprint $table) {
            $table->id();
            $table->integer('id_user')->nullable();
            $table->integer('id_currency')->nullable();
            $table->string('payment_method', 255)->nullable();
            $table->string('ref', 255);
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->float('amount')->nullable();
            $table->string('url_payment', 255)->nullable();
            $table->float('fees')->nullable();
            $table->string('video', 255)->nullable();
            $table->string('image', 255)->nullable();
            $table->string('forfait', 255)->nullable();
            $table->string('website', 255)->nullable();
            $table->integer('payment_status');
            $table->integer('visibility_status');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pack_pubs');
    }
};
