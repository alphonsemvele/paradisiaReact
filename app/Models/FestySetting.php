<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FestySetting extends Model
{
    protected $fillable = ['titre', 'sous_titre', 'date_label', 'prix', 'description', 'inscriptions_ouvertes'];
    protected $casts = ['inscriptions_ouvertes' => 'boolean'];

    /** Réglages uniques (crée la ligne par défaut si absente). */
    public static function actuel(): self
    {
        return static::firstOrCreate([], ['titre' => 'PARADISIA FESTY']);
    }
}
