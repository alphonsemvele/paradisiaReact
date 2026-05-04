<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ["name","description","status","price","id_category","id_user","img_1","img_2"];


    public function user()
    {
         return $this->belongsTo(User::class,'id_user');

    }

     public function categories()
    {
         return $this->belongsTo(Category::class,'id_category');

    }
    
}
