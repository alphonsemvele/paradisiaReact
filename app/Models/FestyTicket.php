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
        'reference', 'code_ticket', 'user_id', 'festy_team_id', 'zone', 'dossard', 'type',
        'montant', 'devise', 'promo', 'moyen', 'statut', 'payment_country',
        'telephone', 'ref_paiement_api', 'preuve', 'error_code', 'valide_par', 'paid_at',
    ];

    protected $casts = [
        'montant' => 'integer',
        'dossard' => 'integer',
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

    /** Zones de participation et nombre de dossards par zone. */
    public const ZONES = ['yaounde' => 'Yaoundé', 'douala' => 'Douala'];

    /**
     * Nombre de dossards par zone, propre à chaque formule : participants
     * 001..160, fans 001..300. Les deux séries sont indépendantes (le dossard
     * 042 participant et le 042 fan sont deux personnes différentes).
     */
    public const DOSSARD_MAX = ['participant' => 160, 'fan' => 300];

    /** Dossard maximum pour une formule donnée. */
    public static function dossardMax(string $type): int
    {
        return self::DOSSARD_MAX[$type] ?? self::DOSSARD_MAX['participant'];
    }

    public function typeLibelle(): string
    {
        return $this->type === 'fan' ? 'Fan' : 'Participant';
    }

    public function zoneLibelle(): ?string
    {
        return $this->zone ? (self::ZONES[$this->zone] ?? $this->zone) : null;
    }

    /** Dossard sur 3 chiffres (ex. 42 → « 042 »). */
    public function dossardFormate(): ?string
    {
        return $this->dossard ? str_pad((string) $this->dossard, 3, '0', STR_PAD_LEFT) : null;
    }
}
