<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FestyRegistration extends Model
{
    protected $fillable = ['festy_team_id', 'nom', 'prenom', 'telephone', 'email', 'ville', 'quartier', 'ip'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(FestyTeam::class, 'festy_team_id');
    }
}
