<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BannedIp extends Model
{
    protected $fillable = ['ip', 'raison'];

    protected static function booted(): void
    {
        // Le cache de la liste (utilisé par le middleware) est invalidé à
        // chaque ajout / suppression.
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public const CACHE_KEY = 'banned_ips_set';

    /** Ensemble des IP bannies (mémorisé 5 min). */
    public static function ensemble(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, fn () => self::pluck('ip')->all());
    }

    public static function estBannie(?string $ip): bool
    {
        return $ip !== null && in_array($ip, self::ensemble(), true);
    }
}
