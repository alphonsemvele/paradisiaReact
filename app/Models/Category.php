<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ["name","description","status","id_user"];

    public function user()
    {

         return $this->belongsTo(User::class,'id_user');

    }
    public function products()
    {

         return $this->hasMany(Product::class,'id_category');

    }
}
