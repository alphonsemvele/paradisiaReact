<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductComponent extends Model
{
    protected $fillable = [
        'id_composite_product',
        'id_component_product',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /** Le produit composé (ex : carton). */
    public function composite(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_composite_product');
    }

    /** Le produit simple qui compose (ex : bouteille). */
    public function component(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_component_product');
    }
}
