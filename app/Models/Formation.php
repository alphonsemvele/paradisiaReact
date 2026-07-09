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
}
