<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    protected $fillable = ['sujet', 'contenu', 'cible', 'total', 'envoyes', 'echecs', 'statut', 'id_admin', 'termine_at'];
    protected $casts = ['termine_at' => 'datetime'];

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'campaign_id');
    }
}
