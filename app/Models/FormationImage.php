<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormationImage extends Model
{
    protected $fillable = [
        'formation_id',
        'path',
        'position',
    ];

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
    }
}
