<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    protected $fillable = [
        'event_id',
        'email',
        'nom',
        'pays',
        'telephone',
        'profil',
        'ip',
        'lien_envoye',
        'lien_envoye_at',
    ];

    protected function casts(): array
    {
        return [
            'lien_envoye' => 'boolean',
            'lien_envoye_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function profilLabel(): string
    {
        return match ($this->profil) {
            'investisseur' => 'Investisseur',
            'participant' => 'Participant',
            default => $this->profil ?: '—',
        };
    }
}
