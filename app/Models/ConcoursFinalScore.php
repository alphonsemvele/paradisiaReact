<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConcoursFinalScore extends Model
{
    protected $fillable = ['id_user', 'reponses_justes'];
}
