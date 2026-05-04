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

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 255)->nullable();
            $table->foreignId('id_round')->nullable()->constrained('rounds', 'round');
            $table->foreignId('id_project')->nullable()->constrained('projects', 'project');
            $table->foreignId('id_publication')->nullable()->constrained('publications', 'publication');
            $table->foreignId('id_user')->constrained('users', 'user');
            $table->foreignId('id_agent')->nullable()->constrained('users', 'agent');
            $table->float('amount');
            $table->float('total_amount')->nullable();
            $table->float('fees')->default('0');
            $table->foreignId('id_fees')->nullable()->constrained('fees', 'fees');
            $table->string('currency', 10)->nullable();
            $table->enum('services', ["invest","don","studyFees","pub"]);
            $table->float('share')->nullable();
            $table->enum('status', ["pending","Failed","Success","Refunded","Cancelled"]);
            $table->enum('type_paiement', ["Contact","Mobile","Bank","Bonus"]);
            $table->string('error_code', 255)->nullable();
            $table->string('customer_number', 255)->nullable();
            $table->string('payment_number', 255)->nullable();
            $table->string('customer_email', 255)->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_postal_code', 255)->nullable();
            $table->text('description_payment')->nullable();
            $table->string('http_request', 500)->nullable();
            $table->string('ip_adress', 100)->nullable();
            $table->string('network_adress', 255)->nullable();
            $table->string('payment_country', 255)->nullable();
            $table->string('url_payment', 255)->nullable();
            $table->integer('payment_country_code')->nullable();
            $table->decimal('fees_operator', 5, 2)->nullable();
            $table->string('operator_after_notify_payment', 255)->nullable();
            $table->longText('ref_paiement_api')->nullable();
            $table->longText('sign_operator')->nullable();
            $table->foreignId('id_operator')->nullable()->constrained('payment_operator_countries', 'operator');
            $table->longText('code_agent_temporaire')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
