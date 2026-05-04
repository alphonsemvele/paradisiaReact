<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_user',
        'id_publication',
        'id_page',
        'body',
        'status',
        'parent_id',
    ];

    /**
     * L'utilisateur qui a écrit le commentaire
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * La publication associée au commentaire
     */
    public function publication()
    {
        return $this->belongsTo(Publication::class, 'id_publication');
    }

    /**
     * La page associée au commentaire
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'id_page');
    }

    /**
     * Le commentaire parent (si c'est une réponse)
     */
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Les réponses à ce commentaire
     */
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    /**
     * Vérifier si c'est une réponse
     */
    public function isReply()
    {
        return $this->parent_id !== null;
    }

    /**
     * Scope pour les commentaires principaux (pas des réponses)
     */
    public function scopeParentComments($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope pour les commentaires actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Success');
    }
}