<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Material extends Model
{
    protected $fillable = ['nom', 'type', 'unite', 'stock', 'seuil_alerte', 'consignable', 'actif'];

    protected $casts = [
        'stock' => 'decimal:2',
        'seuil_alerte' => 'decimal:2',
        'consignable' => 'boolean',
        'actif' => 'boolean',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Enregistre un mouvement de stock et met à jour le stock de façon atomique.
     * $quantite est SIGNÉE : positive = entrée, négative = sortie.
     */
    public static function mouvement(int $materialId, string $type, float $quantite, array $opts = []): StockMovement
    {
        return DB::transaction(function () use ($materialId, $type, $quantite, $opts) {
            /** @var self $m */
            $m = self::lockForUpdate()->findOrFail($materialId);
            $m->stock = (float) $m->stock + $quantite;
            $m->save();

            return $m->movements()->create([
                'type' => $type,
                'quantite' => $quantite,
                'stock_apres' => $m->stock,
                'motif' => $opts['motif'] ?? null,
                'ref_type' => $opts['ref_type'] ?? null,
                'ref_id' => $opts['ref_id'] ?? null,
                'id_user' => $opts['id_user'] ?? auth()->id(),
            ]);
        });
    }

    public function enAlerte(): bool
    {
        return $this->seuil_alerte > 0 && $this->stock <= $this->seuil_alerte;
    }
}
