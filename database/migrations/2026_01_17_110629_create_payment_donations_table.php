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

        Schema::create('payment_donations', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 255);
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_email', 255)->nullable();
            $table->foreignId('id_user')->nullable()->constrained('users', 'user');
            $table->integer('amount');
            $table->string('country', 255)->nullable();
            $table->string('payment_country', 255);
            $table->string('payment_country_code', 255);
            $table->string('currency', 255);
            $table->foreignId('id_project')->constrained('projects', 'project');
            $table->enum('status', ["pending","Success","failed","waiting"]);
            $table->enum('type_paiement', ["Contact","Mobile","Bank"]);
            $table->string('payment_number', 255)->nullable();
            $table->foreignId('id_fees')->constrained('fees', 'fees');
            $table->integer('fees')->nullable();
            $table->integer('total_amount')->nullable();
            $table->string('customer_number', 255)->nullable();
            $table->longText('description')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_donations');
    }
};
