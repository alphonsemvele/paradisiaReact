<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Share extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_project',
        'id_publication',
        'id_user',
        'id_page',
        'ip_address',
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
            'id_project' => 'integer',
            'id_publication' => 'integer',
            'id_user' => 'integer',
            'id_page' => 'integer',
        ];
    }

    public function idProject(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function Publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class,'id_publication');
    }

    public function idUser(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function idPage(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
