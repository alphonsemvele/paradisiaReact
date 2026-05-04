<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_user',
        'category_id',
        'type',
        'name',
        'description',
        'public_key',
        'secret_key',
        'website_url',
        'status',
        'status_invest',
        'objective',
        'currency',
        'project_book',
        'private_policy',
        'business_plan',
        'logo_125_125',
        'img_banniere_1202_425',
        'organizer_name',
        'organizer_address',
        'organizer_city',
        'organizer_email',
        'organizer_cdial',
        'organizer_phone',
        'organizer_cname',
        'organizer_country_code',
        'organizer_website_url',
        'organizer_country',
        'cachet',
        'registre_comm',
        'numero_cont',
        'video',
        'pack_vue',
        'duration',
        'facebook',
        'twitter',
        'instagram',
        'youtube',
        'cni',
        'logo_105_200',
        'img_banniere_263_240',
        'sigle',
        'contract_color',
        'feesStudy',
        'feesStudyValue',
        'category_project_id',
        'user_id',
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
            'category_id' => 'integer',
            'objective' => 'float',
            'category_project_id' => 'integer',
            'user_id' => 'integer',
        ];
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    public function publicities(): HasMany
    {
        return $this->hasMany(Publicity::class);
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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentDonations(): HasMany
    {
        return $this->hasMany(PaymentDonation::class);
    }

    public function categoryProject(): BelongsTo
    {
        return $this->belongsTo(CategoryProject::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function idUser(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryProject::class);
    }
}
