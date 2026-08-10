<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'titre',
        'description',
        'type',
        'mode',
        'lieu',
        'date_debut',
        'date_fin',
        'image',
        'document',
        'collecte_pays',
        'collecte_profil',
        'collecte_telephone',
        'collecte_nom',
        'email_optionnel',
        'message_confirmation',
        'lien_reunion',
        'statut',
        'inscriptions_ouvertes',
        'places_max',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'datetime',
            'date_fin' => 'datetime',
            'collecte_pays' => 'boolean',
            'collecte_profil' => 'boolean',
            'collecte_telephone' => 'boolean',
            'collecte_nom' => 'boolean',
            'email_optionnel' => 'boolean',
            'inscriptions_ouvertes' => 'boolean',
            'places_max' => 'integer',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /** Galerie d'images (l'attribut image reste la couverture). */
    public function images(): HasMany
    {
        return $this->hasMany(EventImage::class)->orderBy('position');
    }

    public function estPasse(): bool
    {
        return $this->date_debut->isPast();
    }

    public function modeLabel(): string
    {
        return match ($this->mode) {
            'presentiel' => 'En présentiel',
            'hybride' => 'Hybride',
            default => 'En ligne',
        };
    }

    /**
     * Les inscriptions sont-elles réellement possibles ? (publié, ouvert,
     * pas encore passé, places restantes)
     */
    public function accepteInscriptions(): bool
    {
        if ($this->statut !== 'publie' || ! $this->inscriptions_ouvertes) {
            return false;
        }

        if ($this->date_debut->isPast()) {
            return false;
        }

        if ($this->places_max !== null && $this->registrations()->count() >= $this->places_max) {
            return false;
        }

        return true;
    }
}
