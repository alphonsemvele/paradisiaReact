<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inscription extends Model
{
    protected $fillable = [
        'formation_id',
        'nom',
        'prenom',
        'telephone',
        'type',
        'statut',
    ];

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
    }

    /**
     * Libellé lisible du type de formation.
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'acceleree' ? 'Accélérée' : 'Normale';
    }
}
