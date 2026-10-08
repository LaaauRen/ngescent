<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'label', 'sku', 'price', 'stock', 'sort_order'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** 389000 -> "389rb", 42500 -> "42,5rb" */
    public function getShortPriceAttribute(): string
    {
        $k = $this->price / 1000;
        return (floor($k) == $k ? (string) (int) $k : str_replace('.', ',', number_format($k, 1, '.', ''))) . 'rb';
    }
}
