<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentDonation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ref',
        'customer_name',
        'customer_email',
        'id_user',
        'amount',
        'country',
        'payment_country',
        'payment_country_code',
        'currency',
        'id_project',
        'status',
        'type_paiement',
        'payment_number',
        'id_fees',
        'fees',
        'total_amount',
        'customer_number',
        'description',
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
            'id_project' => 'integer',
            'id_fees' => 'integer',
        ];
    }

    public function idUser(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function idProject(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function idFees(): BelongsTo
    {
        return $this->belongsTo(Fee::class);
    }
}
