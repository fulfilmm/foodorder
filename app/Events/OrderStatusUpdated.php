<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\Channel;

class OrderStatusUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $order;

    public function __construct($order)
    {
        // Convert to array or use only necessary fields
        // $this->order = $order->only(['id', 'status']);
        $this->order = $order;
    // $this->order->status_timestamps = $this->order->statusHistory()
    //    ->get()
    //    ->pluck('created_at', 'status')
    //    ->toArray();
    $this->order->status_timestamps = $this->order->status_timestamps;
    }

    public function broadcastOn()
    {
        return new Channel('orders');
    }

    public function broadcastAs()
    {
        return 'OrderStatusUpdated';
    }
}


