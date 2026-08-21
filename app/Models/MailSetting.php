<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MailSetting extends Model
{
    protected $fillable = ['actif', 'mailer', 'host', 'port', 'username', 'password', 'encryption', 'from_address', 'from_name'];

    protected $casts = [
        'actif' => 'boolean',
        'port' => 'integer',
        'password' => 'encrypted', // stocké chiffré en base
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('mail_settings'));
    }

    public static function actuel(): self
    {
        return static::firstOrCreate([], ['from_name' => 'Paradisia', 'from_address' => 'no-reply@paradisia-africa.com']);
    }

    /** Réglage mémorisé (pour l'appliquer à chaque requête sans requête DB à chaque fois). */
    public static function cache(): ?self
    {
        return Cache::remember('mail_settings', 300, fn () => static::first());
    }
}
