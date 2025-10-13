<?php

namespace App\Http\Controllers\Kitchen;

use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;


class KitchenController extends Controller
{


// public function kitchenOrders(Request $request)
// {
//     // $orders = Order::with('items.product')
//     //     ->latest()
//     //     ->get()
//     //     ->groupBy('status');
//     $orders = Order::with('items.product')
//     ->whereDate('created_at', Carbon::today()) // ✅ Only today
//     ->latest()
//     ->get()
//     ->groupBy('status');

//     return view('kitchen.home', compact('orders'));
// }
// public function kitchenOrders(Request $request)
// {
// //    $today = Carbon::now()->timezone(config('app.timezone'))->startOfDay();
// //    $tomorrow = Carbon::now()->timezone(config('app.timezone'))->endOfDay();
//     $orders = Order::with('items.product')
//         ->whereDate('created_at', Carbon::today()) // ✅ Only today's orders
//         ->whereNotIn('status', ['eating', 'done'])
//         ->latest() // ✅ Order by created_at DESC
//         ->get()
//         ->groupBy('status') // ✅ Group by status
//         ->map(function (Collection $group) {
//             return $group->sortByDesc('created_at'); // ✅ Sort each group DESC by creation time
//         });

// // e.g. "Tue Jul 22 2025"

// //    dd($orders,Carbon::today(),Carbon::now(),Date::today());

//     return view('kitchen.home', compact('orders'));
// }
 public function kitchenOrders(Request $request)
    {
        $q = trim($request->get('q', ''));

        $query = Order::with(['items.product', 'table'])
            ->whereDate('created_at', Carbon::today())
            ->whereNotIn('status', ['eating', 'done']); // hide eaten/done in kitchen view

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('order_no', 'like', "%{$q}%")
                    ->orWhereHas('items', function ($i) use ($q) {
                        $i->where('name', 'like', "%{$q}%")
                          ->orWhereHas('product', function ($p) use ($q) {
                              $p->where('name', 'like', "%{$q}%");
                          });
                    });
            });
        }

        $orders = $query->latest()->get()
            ->groupBy('status')
            ->map(function (Collection $group) {
                return $group->sortByDesc('created_at');
            });

        return view('kitchen.home', [
            'orders' => $orders,
            'q'      => $q,
        ]);
    }

// public function updateStatus(Request $request, Order $order)
// {
//     $currentStatus = $order->status;

//     $statusFlow = [
//         'pending'    => 'confirmed',
//         'confirmed'  => 'preparing',
//         'preparing'  => 'delivered',
//         'delivered'  => 'eating',
//         'eating'     => 'done',
//     ];

//     if (isset($statusFlow[$currentStatus])) {
//         $order->status = $statusFlow[$currentStatus];
//         $order->save();

//         return response()->json(['success' => true, 'status' => $order->status]);
//     }

//     return response()->json(['success' => false, 'message' => 'No next status.'], 400);
// }
public function updateStatus(Request $request, Order $order)
{
    $statusFlow = [
        'pending'    => 'confirmed',
        'confirmed'  => 'preparing',
        'preparing'  => 'delivered',
        'delivered'  => 'eating',
        'eating'     => 'done',
    ];

    $current = $order->status;

    if (!array_key_exists($current, $statusFlow)) {
        return response()->json([
            'success' => false,
            'message' => 'Cannot change status'
        ], 400);
    }

    $order->status = $statusFlow[$current];
    $order->save();
    $order->statusHistory()->create([
        'status' => $order->status,
        'changed_at' => Carbon::now(),
    ]);
    event(new \App\Events\OrderStatusUpdated($order));
    // event(new \App\Events\OrderStatusUpdated($order));
    Log::info('🔊 Broadcasting order update', $order->only(['id', 'status']));

    // broadcast(new OrderStatusUpdated($order))->toOthers();


    return response()->json([
        'success' => true,
        'status' => $order->status,
        'message' => 'Status updated'
    ]);
}

    public function cancel(Request $request, Order $order)
    {
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending or confirmed orders can be canceled.'
            ], 400);
        }

        $order->status = 'canceled';
        $order->save();

        $order->statusHistory()->create([
            'status' => 'canceled',
            'changed_at' => Carbon::now(),
        ]);

        event(new OrderStatusUpdated($order));
        Log::info('🔴 Order canceled', $order->only(['id', 'status']));

        return response()->json([
            'success' => true,
            'status' => 'canceled',
            'message' => 'Order has been canceled.'
        ]);
    }
}
