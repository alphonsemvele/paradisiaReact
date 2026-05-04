<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ref',
        'id_father',
        'name',
        'last_name',
        'email',
        'password',
        'role',
        'valid',
        'confirmed',
        'confirmation_code',
        'default_currency',
        'referral_code',
        'status',
        'country',
        'phone',
        'ville',
        'last_connect',
        'cover_img',
        'last_active',
        'photo',
        'description',
        'birth',
        'country_code',
        'recoveryPass_code',
        'sexe',
        'id_country',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'id_father' => 'integer',
            'valid' => 'integer',
            'confirmed' => 'integer',
            'last_connect' => 'datetime',
            'last_active' => 'datetime',
            'birth' => 'date',
            'id_country' => 'integer',
        ];
    }
public function categories(): HasMany
    {
        return $this->hasMany(Category::class,'id_user');
    }
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class,'id_user');
    }

    public function publicities(): HasMany
    {
        return $this->hasMany(Publicity::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(Share::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(View::class);
    }

    public function connecteds(): HasMany
    {
        return $this->hasMany(Connected::class);
    }

    public function deconnecteds(): HasMany
    {
        return $this->hasMany(Deconnected::class);
    }

    public function askQuestions(): HasMany
    {
        return $this->hasMany(AskQuestion::class);
    }

    public function creditAgents(): HasMany
    {
        return $this->hasMany(CreditAgent::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class,'id_user');
    }

    public function paymentDonations(): HasMany
    {
        return $this->hasMany(PaymentDonation::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    public function abonnements(): HasMany
    {
        return $this->hasMany(Abonnement::class);
    }

    public function idFather(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

     public function products()
    {

         return $this->hasMany(Product::class,'id_user');

    }
}
