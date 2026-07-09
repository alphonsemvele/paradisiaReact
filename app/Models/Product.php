<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ["name","description","status","price","type","id_category","id_user","img_1","img_2"];


    public function user()
    {
         return $this->belongsTo(User::class,'id_user');

    }

     public function categories()
    {
         return $this->belongsTo(Category::class,'id_category');

    }

    /** Composition (si produit composé) : lignes produit simple + quantité. */
    public function components(): HasMany
    {
        return $this->hasMany(ProductComponent::class, 'id_composite_product');
    }

    public function isComposite(): bool
    {
        return $this->type === 'compose';
    }
}
