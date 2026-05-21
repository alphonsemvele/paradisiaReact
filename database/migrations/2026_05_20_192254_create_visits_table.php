<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('session_id', 100)->nullable()->index();
            $table->foreignId('id_user')->nullable()->index();
            $table->string('url', 500);
            $table->string('path', 255)->index();
            $table->string('referer', 500)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('device_type', 20)->nullable()->index();  // mobile, desktop, tablet
            $table->string('browser', 50)->nullable();
            $table->string('os', 50)->nullable();
            $table->string('country', 100)->nullable()->index();
            $table->string('country_code', 5)->nullable();
            $table->string('city', 100)->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index(['session_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};