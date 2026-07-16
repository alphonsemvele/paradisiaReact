<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Formation extends Model
{
    protected $fillable = [
        'titre',
        'description',
        'prix',
        'prix_inscription',
        'duree',
        'session',
        'mode',
        'image',
        'document',
        'statut',
    ];

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class);
    }

    /** Galerie d'images (l'attribut image reste la couverture). */
    public function images(): HasMany
    {
        return $this->hasMany(FormationImage::class)->orderBy('position');
    }
}
