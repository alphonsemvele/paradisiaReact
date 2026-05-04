<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Publication extends Model
{
    use HasFactory;

    /**
     * Les champs remplissables en masse
     */
    protected $fillable = [
        'ref',
        'title',
        'text',
        'id_user',
        'id_project',
        'id_page',
        'status',
        'nbr_vews',
        'img_1',
        'img_2',
        'img_3',
        'img_4',
        'img_5',
        'video',
        'audio',
        'type',
        'typeServicePublicity',
        'country',
        'id_country',
        'ip_address',
        'attachment',
        'amount',
    ];

    /**
     * Casts des attributs
     */
    protected function casts(): array
    {
        return [
            'id'          => 'integer',
            'id_user'     => 'integer',
            'id_project'  => 'integer',
            'id_page'     => 'integer',
            'id_country'  => 'integer',
            'nbr_vews'    => 'integer',
            'amount'      => 'integer',
        ];
    }

    /**
     * Génération automatique de la référence unique au format PUB-AAAAMMJJ-NNNNN
     */
    protected static function booted()
    {
        static::creating(function ($publication) {
            $date = now()->format('Ymd'); // ex: 20260118
            $prefix = "PUB-{$date}-";

            // Trouver le dernier numéro du jour
            $lastNumber = DB::table('publications')
                ->where('ref', 'like', $prefix . '%')
                ->max(DB::raw("CAST(SUBSTRING_INDEX(ref, '-', -1) AS UNSIGNED)")) ?? 0;

            $nextNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

            $publication->ref = $prefix . $nextNumber;
        });
    }

    // ────────────────────────────────────────────────
    // Relations
    // ────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'id_project');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'id_page');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'id_country');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'id_publication');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class, 'id_publication');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(Share::class, 'id_publication');
    }

    public function views(): HasMany
    {
        return $this->hasMany(View::class, 'id_publication');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}