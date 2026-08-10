<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FestyTeam extends Model
{
    protected $fillable = ['nom', 'trait', 'couleur', 'emoji', 'image', 'whatsapp_group', 'actif', 'position'];
    protected $casts = ['actif' => 'boolean'];

    public function registrations(): HasMany
    {
        return $this->hasMany(FestyRegistration::class);
    }
}
