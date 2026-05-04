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

        Schema::create('payment_operator_countries', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 255);
            $table->string('name', 255);
            $table->longText('api_end_point')->nullable();
            $table->string('currency', 255);
            $table->decimal('fees', 5, 2)->nullable();
            $table->enum('status', ["pending","Success","failed"]);
            $table->string('country_code', 11);
            $table->integer('country_cdial');
            $table->longText('message')->nullable();
            $table->tinyInteger('otpOption')->default(0);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_operator_countries');
    }
};
