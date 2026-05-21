<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'session_id',
        'id_user',
        'url',
        'path',
        'referer',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'country',
        'country_code',
        'city',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}