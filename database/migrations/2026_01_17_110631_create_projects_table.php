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

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users', 'user');
            $table->foreignId('category_id')->nullable()->constrained('category_projects');
            $table->enum('type', ["invest","don"]);
            $table->string('name', 500);
            $table->longText('description')->nullable();
            $table->string('public_key', 150)->unique()->nullable();
            $table->string('secret_key', 150)->unique()->nullable();
            $table->string('website_url', 500)->nullable();
            $table->enum('status', ["pending","Success","failed","waiting"]);
            $table->enum('status_invest', ["pending","invest","vote","trade","deleted","end"]);
            $table->float('objective');
            $table->string('currency', 5);
            $table->string('project_book', 255);
            $table->string('private_policy', 255);
            $table->string('business_plan', 255);
            $table->string('logo_125_125', 255);
            $table->string('img_banniere_1202_425', 100)->nullable();
            $table->string('organizer_name', 255)->nullable();
            $table->text('organizer_address')->nullable();
            $table->string('organizer_city', 255)->nullable();
            $table->string('organizer_email', 255)->nullable();
            $table->string('organizer_cdial', 10)->nullable();
            $table->string('organizer_phone', 255)->nullable();
            $table->string('organizer_cname', 255)->nullable();
            $table->string('organizer_country_code', 10)->nullable();
            $table->string('organizer_website_url', 300)->nullable();
            $table->string('organizer_country', 255)->nullable();
            $table->string('cachet', 255)->nullable();
            $table->string('registre_comm', 255)->nullable();
            $table->string('numero_cont', 255)->nullable();
            $table->string('video', 255)->nullable();
            $table->integer('pack_vue')->nullable();
            $table->string('duration', 3);
            $table->string('facebook', 255)->nullable();
            $table->string('twitter', 255)->nullable();
            $table->string('instagram', 255)->nullable();
            $table->string('youtube', 255)->nullable();
            $table->string('cni', 255);
            $table->string('logo_105_200', 255)->nullable();
            $table->string('img_banniere_263_240', 255)->nullable();
            $table->string('sigle', 20);
            $table->string('contract_color', 255)->nullable();
            $table->enum('feesStudy', ["0","1"]);
            $table->integer('feesStudyValue')->default(50000);
            $table->foreignId('category_project_id');
            $table->foreignId('user_id');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
