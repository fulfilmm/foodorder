<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{

    public function home()
    {
        $user = Auth::user();
        $orders = Order::with(['customer', 'items', 'table'])->orderBy('created_at', 'desc')->get();
        foreach ($orders as $order) {
            if ($order->has_add_on) {
                $order->add_on_count = Order::where('parent_order_id', $order->id)->count();
            } else {
                $order->add_on_count = 0;
            }
        }

        return view('manager.home.home', compact('user', 'orders'));
    }

    public function ajaxDashboardStats(Request $request)
    {
        $range = $request->input('range', 'today');
        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->endOfDay();

        switch ($range) {
            case 'weekly':
                $start = Carbon::now()->startOfWeek();
                $end = Carbon::now()->endOfWeek();
                break;
            case 'monthly':
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
                break;
            case 'yearly':
                $start = Carbon::now()->startOfYear();
                $end = Carbon::now()->endOfYear();
                break;
            case 'custom':
                $start = Carbon::parse($request->input('start'));
                $end = Carbon::parse($request->input('end'));
                break;

            case 'custom-month':
                $month = Carbon::parse($request->input('month'));
                $start = $month->copy()->startOfMonth();
                $end = $month->copy()->endOfMonth();
                break;

            case 'custom-year':
                $year = $request->input('year');
                $start = Carbon::create($year, 1, 1)->startOfDay();
                $end = Carbon::create($year, 12, 31)->endOfDay();
                break;
        }


        $orders = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $salesTakeaway = DB::table('orders')
            ->where('order_type', 'takeaway')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total');

        $salesDinein = DB::table('orders')
            ->where('order_type', 'dine_in')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total');

        $customers = DB::table('users')
            ->where('role', 'customer')
            ->whereBetween('created_at', [$start, $end])
            ->count();
        if ($request->range === 'all') {
            $orders = Order::count();
            $sales_takeaway = Order::where('type', 'takeaway')->sum('total');
            $sales_dinein = Order::where('type', 'dinein')->sum('total');
            $customers = User::where('role', 'customer')
                ->count();;

            return response()->json([
                'orders' => $orders,
                'sales_takeaway' => $sales_takeaway,
                'sales_dinein' => $sales_dinein,
                'customers' => $customers
            ]);
        }

        return response()->json([
            'orders' => $orders,
            'sales_takeaway' => $salesTakeaway,
            'sales_dinein' => $salesDinein,
            'customers' => $customers,
        ]);
    }
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:preparing,pending,confirmed,delivered,eating,done,canceled',
        ]);

        $order->status = $request->status;
        // $order->save();
        // $order->statusHistory()->create([
        //     'status' => $order->status,
        //     'changed_at' => Carbon::now(),
        // ]);
        $order->updateStatus($request->status);

        return response()->json(['success' => true]);
    }
    public function filter(Request $request)
    {
        $query = Order::with(['customer', 'items', 'table']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->order_type) {
            $query->where('order_type', $request->order_type);
        }

        $orders = $query->orderByDesc('created_at')->get();

        foreach ($orders as $order) {
            $order->add_on_count = Order::where('parent_order_id', $order->id)->count();
        }

        $html = view('admin.orders.partials.table_rows', compact('orders'))->render();

        return response()->json(['html' => $html]);
    }
}
