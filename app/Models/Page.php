<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_user',
        'name',
        'description',
        'ref',
        'website_url',
        'status',
        'logo_125_125',
        'img_banniere_1202_425',
        'facebook',
        'twitter',
        'instagram',
        'youtube',
        'sigle',
        'bio',
        'video',
        'whatsapp',
        'country',
        'country_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'id_user' => 'integer',
        ];
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(Share::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(View::class);
    }

    public function abonnements(): HasMany
    {
        return $this->hasMany(Abonnement::class);
    }

    public function idUser(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
