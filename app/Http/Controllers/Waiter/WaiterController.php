<?php

namespace App\Http\Controllers\Waiter;

use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use App\Models\Tax;
use Carbon\Carbon;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WaiterController extends Controller
{


    // public function waiterOrders(Request $request)
    // {
    //     // Today’s dine-in orders, grouped by table
    //     $orders = Order::with('items.product')
    //         ->whereDate('created_at', Carbon::today())
    //         ->where('order_type', 'dine_in')
    //         ->latest()
    //         ->get()
    //         ->groupBy('table_id');

    //     $table            = Table::all();
    //     $categories       = Category::all();
    //     $selectedCategory = $request->query('category');
    //     $products         = Product::with('category')->get();
    //     $firstCategoryName = $categories->first()?->name;

    //     // Active taxes for UI (id, name, percent only)
    //     $activeTaxes = Tax::where('is_active', true)
    //         ->orderByDesc('is_default')
    //         ->orderBy('name')
    //         ->get(['id', 'name', 'percent']);

    //     return view('waiter.home', compact(
    //         'orders',
    //         'table',
    //         'categories',
    //         'selectedCategory',
    //         'products',
    //         'firstCategoryName',
    //         'activeTaxes'
    //     ));
    // }

    public function waiterOrders(Request $request)
    {
        // All tables (adjust ordering as you prefer)
        $tables = Table::orderBy('name')->get();

        // Today’s dine-in orders grouped by table_id (adjust 'order_type' if you store it)
        $orders = Order::with(['items.product'])
            // ->where('order_type', 'dine_in') // uncomment if you track order types
            ->whereDate('created_at', Carbon::today())
            ->whereNotNull('table_id')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('table_id');

        // Categories for the menu section
        $categories = Category::orderBy('name')->get();
        $firstCategoryName = $categories->first()->name ?? null;

        // Active taxes chips (optional)
        $activeTaxes = Tax::where('is_active', true)->get(['name', 'percent']);

        return view('waiter.home', [
            'table'             => $tables,           // keep your original variable name
            'orders'            => $orders,
            'categories'        => $categories,
            'firstCategoryName' => $firstCategoryName,
            'activeTaxes'       => $activeTaxes,
        ]);
    }


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
}
