<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailCampaignRecipient extends Model
{
    protected $table = 'email_campaign_recipients';
    protected $fillable = ['campaign_id', 'user_id', 'email', 'statut', 'envoye_at'];
    protected $casts = ['envoye_at' => 'datetime'];
}
