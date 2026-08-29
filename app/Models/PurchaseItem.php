<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    protected $fillable = ['purchase_id', 'material_id', 'quantite', 'cout_unitaire', 'sous_total'];

    protected $casts = ['quantite' => 'decimal:2', 'cout_unitaire' => 'decimal:2', 'sous_total' => 'decimal:2'];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
