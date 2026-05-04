<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackPub extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_user',
        'id_currency',
        'payment_method',
        'ref',
        'title',
        'description',
        'amount',
        'url_payment',
        'fees',
        'video',
        'image',
        'forfait',
        'website',
        'payment_status',
        'visibility_status',
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
            'amount' => 'float',
            'fees' => 'float',
        ];
    }
}
