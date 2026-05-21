<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointDeVente extends Model
{
    use HasFactory;

    protected $table = 'points_de_vente';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'hours',
        'latitude',
        'longitude',
        'image',
        'status',
        'id_user',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}