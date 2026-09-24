<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ticket PARADISIA FESTY (participant ou fan), rattaché à une équipe et à son
 * acheteur. Payé par MTN Mobile Money ou Orange Money (validation manuelle).
 */
class FestyTicket extends Model
{
    protected $fillable = [
        'reference', 'code_ticket', 'user_id', 'festy_team_id', 'type',
        'montant', 'devise', 'promo', 'moyen', 'statut', 'payment_country',
        'telephone', 'ref_paiement_api', 'preuve', 'error_code', 'valide_par', 'paid_at',
    ];

    protected $casts = [
        'montant' => 'integer',
        'promo' => 'boolean',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(FestyTeam::class, 'festy_team_id');
    }

    public function validePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function typeLibelle(): string
    {
        return $this->type === 'fan' ? 'Fan' : 'Participant';
    }
}
