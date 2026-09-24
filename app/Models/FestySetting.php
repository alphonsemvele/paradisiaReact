<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FestySetting extends Model
{
    protected $fillable = [
        'titre', 'sous_titre', 'date_label', 'prix', 'description', 'inscriptions_ouvertes',
        'prix_participant', 'prix_fan', 'prix_participant_promo', 'prix_fan_promo', 'promo_fin',
        'places_participant_equipe',
    ];

    protected $casts = [
        'inscriptions_ouvertes' => 'boolean',
        'promo_fin' => 'date',
    ];

    /** Réglages uniques (crée la ligne par défaut si absente). */
    public static function actuel(): self
    {
        return static::firstOrCreate([], ['titre' => 'PARADISIA FESTY']);
    }

    /** La promotion est-elle active aujourd'hui ? */
    public function enPromo(): bool
    {
        return $this->promo_fin !== null && now()->startOfDay()->lte($this->promo_fin);
    }

    /**
     * Prix d'un ticket pour un type donné, promotion appliquée le cas échéant.
     *
     * @return array{type:string, montant:int, normal:int, promo:bool}
     */
    public function prixTicket(string $type): array
    {
        $type = $type === 'fan' ? 'fan' : 'participant';

        $normal = (int) ($type === 'fan' ? $this->prix_fan : $this->prix_participant);
        $promoPrix = $type === 'fan' ? $this->prix_fan_promo : $this->prix_participant_promo;

        $enPromo = $this->enPromo() && $promoPrix !== null;

        return [
            'type' => $type,
            'montant' => $enPromo ? (int) $promoPrix : $normal,
            'normal' => $normal,
            'promo' => $enPromo,
        ];
    }
}
