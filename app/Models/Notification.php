<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'body',
        'status',
        'id_project',
        'id_publication',
        'id_user',
        'id_actor',
        'read_at',
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
            'id_project' => 'integer',
            'id_publication' => 'integer',
            'id_user' => 'integer',
        ];
    }

    public function idProject(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function idPublication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }
}
