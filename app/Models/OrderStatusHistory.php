<?php

namespace App\Models;
use App\Models\Order;

use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    protected $fillable = ['order_id', 'status', 'changed_at'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

