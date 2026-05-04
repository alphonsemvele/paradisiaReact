<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Publicity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_user',
        'id_project',
        'object',
        'text',
        'status',
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
            'id_project' => 'integer',
        ];
    }

    public function idUser(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function idProject(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
