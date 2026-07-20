<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Événements Paradisia (meeting, webinaire…) et inscriptions du public.
     *
     * Tout est configurable depuis l'administration : ce que le formulaire
     * demande (pays, profil), le message de confirmation, l'image de la carte
     * d'accueil, le fichier joint, le lien de réunion envoyé le moment venu.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('titre', 180);
            $table->text('description')->nullable();
            $table->string('type', 40)->default('meeting'); // meeting, webinaire, conference…
            $table->enum('mode', ['en_ligne', 'presentiel', 'hybride'])->default('en_ligne');
            $table->string('lieu', 255)->nullable();          // adresse si présentiel
            $table->dateTime('date_debut');
            $table->dateTime('date_fin')->nullable();
            $table->string('image', 255)->nullable();         // carte d'accueil
            $table->string('document', 255)->nullable();      // fichier joint (programme…)

            // Configuration du formulaire d'inscription
            $table->boolean('collecte_pays')->default(true);
            $table->boolean('collecte_profil')->default(true);
            $table->boolean('collecte_telephone')->default(false);
            $table->boolean('collecte_nom')->default(false);
            $table->text('message_confirmation')->nullable();  // e-mail après inscription

            // Lien de la réunion en ligne, envoyé plus tard
            $table->string('lien_reunion', 500)->nullable();

            $table->enum('statut', ['brouillon', 'publie', 'termine'])->default('publie');
            $table->boolean('inscriptions_ouvertes')->default(true);
            $table->unsignedInteger('places_max')->nullable(); // null = illimité
            $table->timestamps();

            $table->index(['statut', 'date_debut']);
        });

        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('email', 180);
            $table->string('nom', 160)->nullable();
            $table->string('pays', 100)->nullable();
            $table->string('telephone', 40)->nullable();
            // « investisseur » ou « participant », configurable via l'événement
            $table->string('profil', 40)->nullable();
            $table->string('ip', 45)->nullable();
            $table->boolean('lien_envoye')->default(false); // le lien de réunion a-t-il été transmis ?
            $table->timestamp('lien_envoye_at')->nullable();
            $table->timestamps();

            // Une même adresse ne s'inscrit qu'une fois par événement
            $table->unique(['event_id', 'email']);
            $table->index(['event_id', 'profil']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('events');
    }
};
