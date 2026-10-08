<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['order_id', 'old_status', 'new_status', 'changed_by', 'note'];
}
