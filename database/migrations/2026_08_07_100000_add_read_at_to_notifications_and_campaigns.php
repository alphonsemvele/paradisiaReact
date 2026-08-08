<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('status');
            }
            // Qui a déclenché la notification (l'auteur du commentaire…)
            if (! Schema::hasColumn('notifications', 'id_actor')) {
                $table->unsignedBigInteger('id_actor')->nullable()->after('id_user');
            }
            $table->index(['id_user', 'read_at']);
        });

        // Campagnes d'e-mails envoyées aux utilisateurs depuis l'administration.
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('sujet', 255);
            $table->longText('contenu');
            $table->string('cible', 40)->default('tous'); // tous, avec_email…
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('envoyes')->default(0);
            $table->unsignedInteger('echecs')->default(0);
            $table->enum('statut', ['brouillon', 'en_cours', 'termine'])->default('brouillon');
            $table->foreignId('id_admin')->nullable();
            $table->timestamp('termine_at')->nullable();
            $table->timestamps();
        });

        // Un destinataire par ligne : permet la reprise et évite les doublons.
        Schema::create('email_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('email_campaigns')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email', 191);
            $table->enum('statut', ['en_attente', 'envoye', 'echec'])->default('en_attente');
            $table->timestamp('envoye_at')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'email']);
            $table->index(['campaign_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaign_recipients');
        Schema::dropIfExists('email_campaigns');

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['read_at', 'id_actor']);
        });
    }
};
