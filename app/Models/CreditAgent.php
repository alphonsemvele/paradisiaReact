<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditAgent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ref',
        'id_user',
        'id_admin',
        'status',
        'amount',
        'currency',
        'commentaire',
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
            'id_admin' => 'integer',
        ];
    }

    public function idUser(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function idAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
