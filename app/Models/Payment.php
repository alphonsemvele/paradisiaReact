<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ref',
        'id_round',
        'id_project',
        'id_publication',
        'id_user',
        'id_agent',
        'amount',
        'total_amount',
        'fees',
        'id_fees',
        'currency',
        'services',
        'share',
        'status',
        'type_paiement',
        'error_code',
        'customer_number',
        'payment_number',
        'customer_email',
        'customer_name',
        'customer_postal_code',
        'description_payment',
        'http_request',
        'ip_adress',
        'network_adress',
        'payment_country',
        'url_payment',
        'payment_country_code',
        'fees_operator',
        'operator_after_notify_payment',
        'ref_paiement_api',
        'sign_operator',
        'id_operator',
        'code_agent_temporaire',
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
            'id_round' => 'integer',
            'id_project' => 'integer',
            'id_publication' => 'integer',
            'id_user' => 'integer',
            'id_agent' => 'integer',
            'amount' => 'float',
            'total_amount' => 'float',
            'fees' => 'float',
            'id_fees' => 'integer',
            'share' => 'float',
            'fees_operator' => 'decimal:2',
            'id_operator' => 'integer',
        ];
    }

    public function idRound(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function idProject(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function idPublication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'id_user');
    }

    public function idAgent(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function idFees(): BelongsTo
    {
        return $this->belongsTo(Fee::class);
    }

    public function idOperator(): BelongsTo
    {
        return $this->belongsTo(PaymentOperatorCountry::class);
    }
}
