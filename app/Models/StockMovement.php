<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = ['material_id', 'type', 'quantite', 'stock_apres', 'motif', 'ref_type', 'ref_id', 'id_user'];

    protected $casts = ['quantite' => 'decimal:2', 'stock_apres' => 'decimal:2'];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
