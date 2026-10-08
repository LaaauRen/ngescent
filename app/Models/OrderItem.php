<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = ['order_id', 'product_id', 'variant_id', 'product_title', 'variant_label', 'image', 'unit_price', 'qty'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getLineTotalAttribute(): int
    {
        return $this->unit_price * $this->qty;
    }
}
