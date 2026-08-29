<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = ['fournisseur', 'date_achat', 'total', 'pour_production', 'note', 'id_user'];

    protected $casts = ['date_achat' => 'date', 'total' => 'decimal:2', 'pour_production' => 'boolean'];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
