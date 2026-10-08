<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public $timestamps = false;
    protected $fillable = ['slug', 'name', 'sort_order'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
