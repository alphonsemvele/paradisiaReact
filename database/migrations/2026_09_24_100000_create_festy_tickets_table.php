<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tickets PARADISIA FESTY : deux formules (participant / fan) liées à une
     * équipe, payées par MTN Mobile Money (automatique) ou Orange Money
     * (code marchand + validation manuelle). Chaque ticket est envoyé par
     * e-mail à son titulaire.
     *
     * Les tarifs sont configurables (prix normal + prix promo jusqu'à une date).
     */
    public function up(): void
    {
        // Tarifs des tickets, ajoutés aux réglages Festy existants.
        Schema::table('festy_settings', function (Blueprint $table) {
            $table->unsignedInteger('prix_participant')->default(7000)->after('prix');
            $table->unsignedInteger('prix_fan')->default(4000)->after('prix_participant');
            $table->unsignedInteger('prix_participant_promo')->nullable()->after('prix_fan');
            $table->unsignedInteger('prix_fan_promo')->nullable()->after('prix_participant_promo');
            $table->date('promo_fin')->nullable()->after('prix_fan_promo');
        });

        // Promotion en cours : 5 000 (participant) / 3 000 (fan) jusqu'au 31/10/2026.
        DB::table('festy_settings')->update([
            'prix_participant' => 7000,
            'prix_fan' => 4000,
            'prix_participant_promo' => 5000,
            'prix_fan_promo' => 3000,
            'promo_fin' => '2026-10-31',
        ]);

        Schema::create('festy_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 40)->unique();      // clé d'idempotence paiement
            $table->string('code_ticket', 24)->unique();     // code lisible imprimé sur le ticket
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('festy_team_id')->nullable()->constrained('festy_teams')->nullOnDelete();
            $table->string('type', 20);                      // participant | fan
            $table->unsignedInteger('montant');              // prix payé
            $table->string('devise', 10)->default('XAF');
            $table->boolean('promo')->default(false);        // prix promo appliqué ?
            $table->string('moyen', 20);                     // mtn | om_manuel
            $table->string('statut', 20)->default('en_attente'); // en_attente | paye | echoue | annule
            $table->string('payment_country', 2)->nullable();
            $table->string('telephone', 30)->nullable();     // numéro payeur (MTN)
            $table->string('ref_paiement_api', 120)->nullable();
            $table->string('preuve', 255)->nullable();       // preuve Orange Money (optionnel)
            $table->string('error_code', 60)->nullable();
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'statut']);
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('festy_tickets');

        Schema::table('festy_settings', function (Blueprint $table) {
            $table->dropColumn([
                'prix_participant', 'prix_fan',
                'prix_participant_promo', 'prix_fan_promo', 'promo_fin',
            ]);
        });
    }
};
