<?php

namespace App\Events;

// use App\Models\Order;
// use Illuminate\Broadcasting\Channel;
// use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
// use Illuminate\Queue\SerializesModels;

// class OrderCreated implements ShouldBroadcast
// {
//     use SerializesModels;

//     public $order;

//     public function __construct(Order $order)
//     {
//         // Load relationships (e.g. items) if needed
//         // $this->order = $order->load('items', 'table');
//         $this->order = $order->loadMissing(['items', 'table']); // ✅ safe even if table_id is null
//     }

//     public function broadcastOn()
//     {
//         return new Channel('orders');
//     }

//     public function broadcastAs()
//     {
//         return 'OrderCreated';
//     }
// }

use App\Models\Order;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\Channel;


class OrderCreated implements ShouldBroadcast
{
    use SerializesModels;

    public int $orderId;

    public function __construct(Order $order)
    {
        $this->orderId = $order->id;
    }

    public function broadcastOn()
    {
        // return new \Illuminate\Broadcasting\Channel('orders');
        return new Channel('orders');

    }

    public function broadcastWith()
    {
        return [
            'order' => Order::with(['items', 'table'])->find($this->orderId),
        ];
    }

    public function broadcastAs()
    {
         return 'OrderCreated';
    }
}
