<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class View extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_user',
        'id_publication',
        'id_project',
        'id_page',
        'status',
        'ip_address',
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
            'id_publication' => 'integer',
            'id_project' => 'integer',
            'id_page' => 'integer',
        ];
    }

    /**
     * La clé étrangère doit être explicite : sans elle, Laravel déduit
     * « id_user_id » du nom de la méthode et la relation renvoie toujours null.
     */
    public function idUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /** Alias lisible de idUser(). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function Publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class,'id_publication');
    }

    public function idProject(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function idPage(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
