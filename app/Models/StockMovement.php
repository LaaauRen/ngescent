<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    public $timestamps = false;
    protected $fillable = ['variant_id', 'order_id', 'change_qty', 'reason', 'created_by'];
}
