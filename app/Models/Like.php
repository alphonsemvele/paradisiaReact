<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_user',
        'id_publication',
        'id_page',
        'ip_address',
        'status',
    ];

    /**
     * L'utilisateur qui a liké
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * La publication likée
     */
    public function publication()
    {
        return $this->belongsTo(Publication::class, 'id_publication');
    }

    /**
     * La page likée
     */
    public function page()
    {
        return $this->belongsTo(Page::class, 'id_page');
    }
}