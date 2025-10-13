<?php

namespace App\Http\Controllers\Customer;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use App\Models\Tax;
use BaconQrCode\Encoder\QrCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

    public function allOrders()
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
        $user = Auth::user();

        if ($user->role == "manager") {
            return view('manager.orders.all-orders', compact('user', 'orders'));
        } else {
            return view('admin.orders.all-orders', compact('user', 'orders'));
        }
    }

    public function filterOrders(Request $request)
    {
        $status = $request->input('status');
        $date = $request->input('date');
        $week = $request->input('week');
        $year = $request->input('year');
        $range = $request->input('range');

        $orders = Order::with(['table', 'customer', 'items'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($date, fn($q) => $q->whereDate('created_at', $date))
            ->when($week, function ($q) use ($week) {
                $dates = explode(" to ", str_replace(" ", "", $week));
                if (count($dates) === 2) {
                    $q->whereBetween('created_at', [$dates[0], $dates[1]]);
                }
            })
            ->when($year, fn($q) => $q->whereYear('created_at', $year))
            ->when($range, function ($q) use ($range) {
                $dates = explode(" to ", str_replace(" ", "", $range));
                if (count($dates) === 2) {
                    $q->whereBetween('created_at', [$dates[0], $dates[1]]);
                }
            })
            ->latest()
            ->get();

        foreach ($orders as $order) {
            $order->add_on_count = $order->has_add_on ? Order::where('parent_order_id', $order->id)->count() : 0;
        }
        $user = Auth::user();

        if ($user->role == "manager") {
            return response()->json([
                'html' => view('manager.orders.partials.orders_table_rows', compact('orders'))->render()
            ]);
        } else {
            return response()->json([
                'html' => view('admin.orders.partials.orders_table_rows', compact('orders'))->render()
            ]);
        }
    }
    // public function show(Request $request, Order $order)
    // {
    //     // Root order (if this is an add-on, go to its parent)
    //     $root = $order->parent_order_id
    //         ? Order::with(['customer', 'table'])->findOrFail($order->parent_order_id)
    //         : $order->load(['customer', 'table']);

    //     // Collect root + add-ons (skip canceled)
    //     $orders = Order::with(['items' => function ($q) {
    //         $q->select('id', 'order_id', 'product_id', 'name', 'price', 'qty', 'comment');
    //     }, 'table'])
    //         ->where(function ($q) use ($root) {
    //             $q->where('id', $root->id)
    //                 ->orWhere('parent_order_id', $root->id);
    //         })
    //         ->whereNotIn('status', ['cancel', 'canceled'])
    //         ->orderBy('created_at')
    //         ->get();

    //     if ($orders->isEmpty()) {
    //         abort(404, 'No billable items for this order.');
    //     }

    //     // Aggregate items (same product/price/comment collapse)
    //     $bucket = [];
    //     foreach ($orders as $o) {
    //         foreach ($o->items as $it) {
    //             $key = ($it->product_id ?? 0) . '|' . (int)$it->price . '|' . trim((string)($it->comment ?? ''));
    //             if (!isset($bucket[$key])) {
    //                 $bucket[$key] = [
    //                     'product_id' => $it->product_id,
    //                     'name'       => $it->name,
    //                     'price'      => (int)$it->price,
    //                     'qty'        => 0,
    //                     'comment'    => $it->comment,
    //                 ];
    //             }
    //             $bucket[$key]['qty'] += (int)$it->qty;
    //         }
    //     }
    //     $items = array_values($bucket);

    //     // Totals + active taxes
    //     $subtotal    = collect($items)->reduce(fn($s, $i) => $s + $i['price'] * $i['qty'], 0);
    //     $activeTaxes = Tax::where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get(['name', 'percent']);
    //     $taxPercent  = (float) $activeTaxes->sum('percent');
    //     $taxAmount   = (int) round($subtotal * ($taxPercent / 100));
    //     $total       = $subtotal + $taxAmount;
    //     $taxSnapshot = $activeTaxes->map(fn($t) => "{$t->name} {$t->percent}%")->implode(' + ');

    //     // Meta for header
    //     $combinedOrderNos = $orders->pluck('order_no')->implode(' + ');
    //     $placeLabel = $root->order_type === 'dine_in'
    //         ? optional($root->table)->name
    //         : 'Takeaway';
    //     $paper = $request->input('paper', '80'); // '80' or '58'
    //     $brand = [
    //         'name'   => config('app.name', 'Your Restaurant'),
    //         'phone'  => config('app.business_phone', ''),   // optional
    //         'addr1'  => config('app.business_address1', ''), // optional
    //         'addr2'  => config('app.business_address2', ''), // optional
    //     ];

    //     return view('admin.orders.slip', [
    //         'paper' => $paper,
    //         'brand' => $brand,
    //         'root'             => $root,
    //         'orders'           => $orders,
    //         'items'            => $items,
    //         'subtotal'         => $subtotal,
    //         'taxPercent'       => $taxPercent,
    //         'taxAmount'        => $taxAmount,
    //         'total'            => $total,
    //         'taxSnapshot'      => $taxSnapshot,
    //         'combinedOrderNos' => $combinedOrderNos,
    //         'placeLabel'       => $placeLabel,
    //         'now'              => Carbon::now(),
    //         'autoPrint'        => (bool)$request->boolean('print'), // ?print=1 to auto open dialog
    //     ]);
    // }
    public function show(Request $request, Order $order)
    {
        // 1) Find root (if $order is add-on, use its parent)
        $root = $order->parent_order_id
            ? Order::with(['customer', 'table', 'parent'])->findOrFail($order->parent_order_id)
            : $order->load(['customer', 'table', 'parent']);

        // 2) Load root + all add-ons (exclude canceled)
        $orders = Order::with([
            'items' => function ($q) {
                $q->select('id', 'order_id', 'product_id', 'name', 'price', 'qty', 'comment');
            },
            'table',
            'customer',
            'parent'
        ])
            ->where(function ($q) use ($root) {
                $q->where('id', $root->id)->orWhere('parent_order_id', $root->id);
            })
            ->whereNotIn('status', ['cancel', 'canceled'])
            ->orderBy('created_at')
            ->get();

        if ($orders->isEmpty()) {
            abort(404, 'No billable items for this order.');
        }

        // 3) Build line items
        $mode = $request->input('mode', 'combined'); // combined | detailed

        // a) Combined: collapse identical items across all sibling orders
        $combinedItems = [];
        foreach ($orders as $o) {
            foreach ($o->items as $it) {
                $key = ($it->product_id ?? 0) . '|' . (int)$it->price . '|' . trim((string)($it->comment ?? ''));
                if (!isset($combinedItems[$key])) {
                    $combinedItems[$key] = [
                        'product_id' => $it->product_id,
                        'name'       => $it->name,
                        'price'      => (int)$it->price,
                        'qty'        => 0,
                        'comment'    => $it->comment,
                    ];
                }
                $combinedItems[$key]['qty'] += (int)$it->qty;
            }
        }
        $items = array_values($combinedItems);

        // b) Detailed sections (Main first, then add-ons)
        $sections = [];
        if ($mode === 'detailed') {
            foreach ($orders as $o) {
                $sections[] = [
                    'title'  => $o->parent_order_id ? "Add-on • #{$o->order_no}" : "Main • #{$o->order_no}",
                    'time'   => $o->created_at,
                    'status' => $o->status,
                    'items'  => $o->items->map(function ($it) {
                        return [
                            'product_id' => $it->product_id,
                            'name'       => $it->name,
                            'price'      => (int)$it->price,
                            'qty'        => (int)$it->qty,
                            'comment'    => $it->comment,
                        ];
                    })->values()->all(),
                    'sum'    => (int) $o->items->reduce(fn($s, $i) => $s + ((int)$i->price * (int)$i->qty), 0),
                ];
            }
        }

        // 4) Subtotal (combined, as the bill is for all)
        $subtotal = collect($items)->reduce(fn($s, $i) => $s + $i['price'] * $i['qty'], 0);

        // 5) Optional discount BEFORE tax (common approach)
        $discountType  = $request->input('discount_type');   // percent|flat
        $discountValue = (float) $request->input('discount_value', 0);
        $discountAmount = 0;
        if ($discountType === 'percent' && $discountValue > 0) {
            $discountAmount = (int) round($subtotal * ($discountValue / 100));
        } elseif ($discountType === 'flat' && $discountValue > 0) {
            $discountAmount = (int) min($subtotal, $discountValue);
        }
        $netSubtotal = max(0, $subtotal - $discountAmount);

        // 6) Taxes (apply on net subtotal)
        $activeTaxes = Tax::where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get(['name', 'percent']);
        $taxPercent  = (float) $activeTaxes->sum('percent');
        $taxAmount   = (int) round($netSubtotal * ($taxPercent / 100));
        $total       = $netSubtotal + $taxAmount;
        $taxSnapshot = $activeTaxes->map(fn($t) => "{$t->name} {$t->percent}%")->implode(' + ');

        // 7) Payment (optional: for receipt-style print)
        $paid   = (int) $request->input('paid', 0);
        $method = $request->input('method', '');   // Cash | Card | KBZ Pay | etc.
        $change = max(0, $paid - $total);

        // 8) Header meta
        $combinedOrderNos = $orders->pluck('order_no')->implode(' + ');
        $placeLabel = $root->order_type === 'dine_in'
            ? optional($root->table)->name
            : 'Takeaway';

        $paper = $request->input('paper', '80'); // '80' or '58'
        $autoPrint = (bool) $request->boolean('print');
        $brand = [
            'name'   => config('app.name', 'Your Restaurant'),
            'phone'  => config('app.business_phone', ''),
            'addr1'  => config('app.business_address1', ''),
            'addr2'  => config('app.business_address2', ''),
        ];



        // 10) Who prints
        $printedBy = optional(Auth::user())->name;

        return view('admin.orders.slip', [
            // print meta
            'paper'            => $paper,
            'autoPrint'        => $autoPrint,
            'brand'            => $brand,
            'now'              => Carbon::now(),
            'printedBy'        => $printedBy,

            // order meta
            'root'             => $root,
            'orders'           => $orders,
            'combinedOrderNos' => $combinedOrderNos,
            'placeLabel'       => $placeLabel,

            // line items (combined + optional detailed sections)
            'mode'             => $mode,
            'items'            => $items,
            'sections'         => $sections,

            // money
            'subtotal'         => $subtotal,
            'discountType'     => $discountType,
            'discountValue'    => $discountValue,
            'discountAmount'   => $discountAmount,
            'netSubtotal'      => $netSubtotal,
            'taxPercent'       => $taxPercent,
            'taxAmount'        => $taxAmount,
            'total'            => $total,
            'paid'             => $paid,
            'method'           => $method,
            'change'           => $change,
            'taxSnapshot'      => $taxSnapshot,

        ]);
    }
    // public function showSlipManager(Request $request, Order $order)
    // {
    //     // Root order (if this is an add-on, go to its parent)
    //     $root = $order->parent_order_id
    //         ? Order::with(['customer', 'table'])->findOrFail($order->parent_order_id)
    //         : $order->load(['customer', 'table']);

    //     // Collect root + add-ons (skip canceled)
    //     $orders = Order::with(['items' => function ($q) {
    //         $q->select('id', 'order_id', 'product_id', 'name', 'price', 'qty', 'comment');
    //     }, 'table'])
    //         ->where(function ($q) use ($root) {
    //             $q->where('id', $root->id)
    //                 ->orWhere('parent_order_id', $root->id);
    //         })
    //         ->whereNotIn('status', ['cancel', 'canceled'])
    //         ->orderBy('created_at')
    //         ->get();

    //     if ($orders->isEmpty()) {
    //         abort(404, 'No billable items for this order.');
    //     }

    //     // Aggregate items (same product/price/comment collapse)
    //     $bucket = [];
    //     foreach ($orders as $o) {
    //         foreach ($o->items as $it) {
    //             $key = ($it->product_id ?? 0) . '|' . (int)$it->price . '|' . trim((string)($it->comment ?? ''));
    //             if (!isset($bucket[$key])) {
    //                 $bucket[$key] = [
    //                     'product_id' => $it->product_id,
    //                     'name'       => $it->name,
    //                     'price'      => (int)$it->price,
    //                     'qty'        => 0,
    //                     'comment'    => $it->comment,
    //                 ];
    //             }
    //             $bucket[$key]['qty'] += (int)$it->qty;
    //         }
    //     }
    //     $items = array_values($bucket);

    //     // Totals + active taxes
    //     $subtotal    = collect($items)->reduce(fn($s, $i) => $s + $i['price'] * $i['qty'], 0);
    //     $activeTaxes = Tax::where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get(['name', 'percent']);
    //     $taxPercent  = (float) $activeTaxes->sum('percent');
    //     $taxAmount   = (int) round($subtotal * ($taxPercent / 100));
    //     $total       = $subtotal + $taxAmount;
    //     $taxSnapshot = $activeTaxes->map(fn($t) => "{$t->name} {$t->percent}%")->implode(' + ');

    //     // Meta for header
    //     $combinedOrderNos = $orders->pluck('order_no')->implode(' + ');
    //     $placeLabel = $root->order_type === 'dine_in'
    //         ? optional($root->table)->name
    //         : 'Takeaway';
    //     $paper = $request->input('paper', '80'); // '80' or '58'
    //     $brand = [
    //         'name'   => config('app.name', 'Your Restaurant'),
    //         'phone'  => config('app.business_phone', ''),   // optional
    //         'addr1'  => config('app.business_address1', ''), // optional
    //         'addr2'  => config('app.business_address2', ''), // optional
    //     ];

    //     return view('manager.orders.slip', [
    //         'paper' => $paper,
    //         'brand' => $brand,
    //         'root'             => $root,
    //         'orders'           => $orders,
    //         'items'            => $items,
    //         'subtotal'         => $subtotal,
    //         'taxPercent'       => $taxPercent,
    //         'taxAmount'        => $taxAmount,
    //         'total'            => $total,
    //         'taxSnapshot'      => $taxSnapshot,
    //         'combinedOrderNos' => $combinedOrderNos,
    //         'placeLabel'       => $placeLabel,
    //         'now'              => Carbon::now(),
    //         'autoPrint'        => (bool)$request->boolean('print'), // ?print=1 to auto open dialog
    //     ]);
    // }
    public function showSlipManager(Request $request, Order $order)
    {
        // 1) Find root (if $order is add-on, use its parent)
        $root = $order->parent_order_id
            ? Order::with(['customer', 'table', 'parent'])->findOrFail($order->parent_order_id)
            : $order->load(['customer', 'table', 'parent']);

        // 2) Load root + all add-ons (exclude canceled)
        $orders = Order::with([
            'items' => function ($q) {
                $q->select('id', 'order_id', 'product_id', 'name', 'price', 'qty', 'comment');
            },
            'table',
            'customer',
            'parent'
        ])
            ->where(function ($q) use ($root) {
                $q->where('id', $root->id)->orWhere('parent_order_id', $root->id);
            })
            ->whereNotIn('status', ['cancel', 'canceled'])
            ->orderBy('created_at')
            ->get();

        if ($orders->isEmpty()) {
            abort(404, 'No billable items for this order.');
        }

        // 3) Build line items
        $mode = $request->input('mode', 'combined'); // combined | detailed

        // a) Combined: collapse identical items across all sibling orders
        $combinedItems = [];
        foreach ($orders as $o) {
            foreach ($o->items as $it) {
                $key = ($it->product_id ?? 0) . '|' . (int)$it->price . '|' . trim((string)($it->comment ?? ''));
                if (!isset($combinedItems[$key])) {
                    $combinedItems[$key] = [
                        'product_id' => $it->product_id,
                        'name'       => $it->name,
                        'price'      => (int)$it->price,
                        'qty'        => 0,
                        'comment'    => $it->comment,
                    ];
                }
                $combinedItems[$key]['qty'] += (int)$it->qty;
            }
        }
        $items = array_values($combinedItems);

        // b) Detailed sections (Main first, then add-ons)
        $sections = [];
        if ($mode === 'detailed') {
            foreach ($orders as $o) {
                $sections[] = [
                    'title'  => $o->parent_order_id ? "Add-on • #{$o->order_no}" : "Main • #{$o->order_no}",
                    'time'   => $o->created_at,
                    'status' => $o->status,
                    'items'  => $o->items->map(function ($it) {
                        return [
                            'product_id' => $it->product_id,
                            'name'       => $it->name,
                            'price'      => (int)$it->price,
                            'qty'        => (int)$it->qty,
                            'comment'    => $it->comment,
                        ];
                    })->values()->all(),
                    'sum'    => (int) $o->items->reduce(fn($s, $i) => $s + ((int)$i->price * (int)$i->qty), 0),
                ];
            }
        }

        // 4) Subtotal (combined, as the bill is for all)
        $subtotal = collect($items)->reduce(fn($s, $i) => $s + $i['price'] * $i['qty'], 0);

        // 5) Optional discount BEFORE tax (common approach)
        $discountType  = $request->input('discount_type');   // percent|flat
        $discountValue = (float) $request->input('discount_value', 0);
        $discountAmount = 0;
        if ($discountType === 'percent' && $discountValue > 0) {
            $discountAmount = (int) round($subtotal * ($discountValue / 100));
        } elseif ($discountType === 'flat' && $discountValue > 0) {
            $discountAmount = (int) min($subtotal, $discountValue);
        }
        $netSubtotal = max(0, $subtotal - $discountAmount);

        // 6) Taxes (apply on net subtotal)
        $activeTaxes = Tax::where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get(['name', 'percent']);
        $taxPercent  = (float) $activeTaxes->sum('percent');
        $taxAmount   = (int) round($netSubtotal * ($taxPercent / 100));
        $total       = $netSubtotal + $taxAmount;
        $taxSnapshot = $activeTaxes->map(fn($t) => "{$t->name} {$t->percent}%")->implode(' + ');

        // 7) Payment (optional: for receipt-style print)
        $paid   = (int) $request->input('paid', 0);
        $method = $request->input('method', '');   // Cash | Card | KBZ Pay | etc.
        $change = max(0, $paid - $total);

        // 8) Header meta
        $combinedOrderNos = $orders->pluck('order_no')->implode(' + ');
        $placeLabel = $root->order_type === 'dine_in'
            ? optional($root->table)->name
            : 'Takeaway';

        $paper = $request->input('paper', '80'); // '80' or '58'
        $autoPrint = (bool) $request->boolean('print');
        $brand = [
            'name'   => config('app.name', 'Your Restaurant'),
            'phone'  => config('app.business_phone', ''),
            'addr1'  => config('app.business_address1', ''),
            'addr2'  => config('app.business_address2', ''),
        ];



        // 10) Who prints
        $printedBy = optional(Auth::user())->name;

        return view('admin.orders.slip', [
            // print meta
            'paper'            => $paper,
            'autoPrint'        => $autoPrint,
            'brand'            => $brand,
            'now'              => Carbon::now(),
            'printedBy'        => $printedBy,

            // order meta
            'root'             => $root,
            'orders'           => $orders,
            'combinedOrderNos' => $combinedOrderNos,
            'placeLabel'       => $placeLabel,

            // line items (combined + optional detailed sections)
            'mode'             => $mode,
            'items'            => $items,
            'sections'         => $sections,

            // money
            'subtotal'         => $subtotal,
            'discountType'     => $discountType,
            'discountValue'    => $discountValue,
            'discountAmount'   => $discountAmount,
            'netSubtotal'      => $netSubtotal,
            'taxPercent'       => $taxPercent,
            'taxAmount'        => $taxAmount,
            'total'            => $total,
            'paid'             => $paid,
            'method'           => $method,
            'change'           => $change,
            'taxSnapshot'      => $taxSnapshot,

        ]);
    }
    public function storeOrder(Request $request)
    {
        $request->validate([
            'pickup_date' => 'required|date_format:d/m/Y',
            'pickup_time' => 'required',
            'phone'       => 'required|regex:/^[0-9]{7,12}$/',
            'order_type'  => 'required|in:dine_in,takeaway',
            // 🔧 use table_id (not table_number) since you save table_id
            'table_id'    => 'nullable|exists:tables,id'
        ]);

        $cart    = session('cart', []);
        $user_id = session('guest_user_id');

        if (empty($cart)) {
            return back()->withErrors(['cart' => 'Your cart is empty.']);
        }

        // --- Build order number for "today" with 5-digit sequence ---
        $lastToday = Order::whereDate('created_at', Carbon::today())->orderByDesc('id')->first();
        $seq       = $lastToday ? (int) substr($lastToday->order_no, -5) + 1 : 1;
        $orderNo   = Carbon::now()->format('dmy') . str_pad($seq, 5, '0', STR_PAD_LEFT);

        // --- Parse pickup date (keep your string time) ---
        $pickupDate = Carbon::createFromFormat('d/m/Y', $request->pickup_date)->format('Y-m-d');

        // --- Subtotal from cart (use the price already in cart line) ---
        $subtotal = array_sum(array_map(
            fn($it) => (int) ($it['price'] ?? 0) * (int) ($it['qty'] ?? 0),
            $cart
        ));

        // --- Apply ALL active taxes; snapshot names & combined percent ---
        $activeTaxes      = Tax::where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get();
        $combinedPercent  = (float) $activeTaxes->sum('percent');
        $taxAmount        = (int) round($subtotal * ($combinedPercent / 100)); // MMK rounded
        $total            = $subtotal + $taxAmount;
        $taxNamesSnapshot = $activeTaxes->map(fn($t) => "{$t->name} {$t->percent}%")->join(' + ');

        // --- Any item comments? ---
        $hasComment = collect($cart)->contains(fn($it) => filled(trim($it['comment'] ?? '')));

        DB::beginTransaction();
        try {
            // Create order
            $order = Order::create([
                'order_no'             => $orderNo,
                'user_id'              => $user_id,
                'phone'                => $request->phone,
                'pickup_date'          => $pickupDate,
                'pickup_time'          => $request->pickup_time,
                'order_type'           => $request->order_type,
                'table_id'             => $request->order_type === 'dine_in' ? ($request->table_id ?? null) : null,
                'status'               => 'pending',

                // 👇 snapshots & totals
                'subtotal'             => $subtotal,
                'tax_amount'           => $taxAmount,
                'total'                => $total,
                'tax_name_snapshot'    => $taxNamesSnapshot,   // e.g. "VAT 7% + Service 5%"
                'tax_percent_snapshot' => $combinedPercent,    // e.g. 12

                'has_comment'          => $hasComment,
            ]);

            // Items & stock
            foreach ($cart as $productId => $item) {
                /** @var Product|null $product */
                $product = Product::find($productId);
                if (!$product || $product->remain_qty < $item['qty']) {
                    DB::rollBack();
                    return back()->withErrors(['stock' => "Insufficient stock for: {$item['name']}"]);
                }

                $product->decrement('remain_qty', (int) $item['qty']);
                $product->increment('sell_qty',   (int) $item['qty']);

                $order->items()->create([
                    'product_id' => $productId,
                    'order_id'   => $order->id,
                    'name'       => $item['name'],
                    'price'      => (int) $item['price'],
                    'qty'        => (int) $item['qty'],
                    'comment'    => filled(trim($item['comment'] ?? '')) ? trim($item['comment']) : null,
                ]);
            }

            // Status history
            $order->statusHistory()->create([
                'status'     => $order->status,
                'changed_at' => Carbon::now(),
            ]);

            // Clean up
            session()->forget('cart');
            DB::commit();

            event(new OrderCreated($order));

            return redirect()
                ->route('customer.take_away.checkout')
                ->with('order_success', true)
                ->with('success', 'Order placed successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            // dd($e); // uncomment while debugging
            return back()->withErrors(['error' => 'Something went wrong. Please try again.']);
        }
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
    public function showOrder(Order $order)
    {
        // Root (main) order + add-ons
        $root = $order->parent_order_id ? Order::with('table')->findOrFail($order->parent_order_id) : $order;
        $root->load(['items.product', 'table', 'statusHistory', 'customer']);

        $addons = Order::with('items.product')
            ->where('parent_order_id', $root->id)
            ->orderBy('id')
            ->get();

        $allOrders = collect([$root])->merge($addons);

        // Combined items (no grouping—keeps comments visible)
        $combinedItems = [];
        foreach ($allOrders as $o) {
            foreach ($o->items as $it) {
                $combinedItems[] = [
                    'order_no'   => $o->order_no,
                    'name'       => $it->name ?? optional($it->product)->name,
                    'qty'        => (int) $it->qty,
                    'price'      => (int) $it->price,
                    'line_total' => (int) $it->price * (int) $it->qty,
                    'comment'    => $it->comment,
                ];
            }
        }

        $subtotal = collect($combinedItems)->sum('line_total');

        // Active taxes snapshot for UI (no DB write)
        $activeTaxes = Tax::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['name', 'percent']);

        $taxPercent = (float) $activeTaxes->sum('percent');
        $taxAmount  = (int) round($subtotal * ($taxPercent / 100));
        $total      = $subtotal + $taxAmount;

        // Timeline from history
        $timeline = $root->statusHistory()
            ->orderBy('changed_at')
            ->get()
            ->map(fn($h) => [
                'status' => $h->status,
                'when'   => optional($h->changed_at)->diffForHumans() ?? $h->created_at->diffForHumans(),
            ]);

        // Next status label
        $flow = ['pending' => 'confirmed', 'confirmed' => 'preparing', 'preparing' => 'delivered', 'delivered' => 'eating', 'eating' => 'done'];
        $next = $flow[$root->status] ?? null;

        $user = Auth::user();

        if ($user->role == "manager") {
            return view('manager.orders.show', [
                'root'          => $root,
                'addons'        => $addons,
                'allOrders'     => $allOrders,
                'combinedItems' => $combinedItems,
                'subtotal'      => $subtotal,
                'activeTaxes'   => $activeTaxes,
                'taxPercent'    => rtrim(rtrim(number_format($taxPercent, 2), '0'), '.'),
                'taxAmount'     => $taxAmount,
                'total'         => $total,
                'timeline'      => $timeline,
                'nextLabel'     => $next ? 'Mark as ' . ucfirst($next) : null,
                'canCancel'     => in_array($root->status, ['pending', 'confirmed']),
            ]);
        } else {
            return view('admin.orders.show', [
                'root'          => $root,
                'addons'        => $addons,
                'allOrders'     => $allOrders,
                'combinedItems' => $combinedItems,
                'subtotal'      => $subtotal,
                'activeTaxes'   => $activeTaxes,
                'taxPercent'    => rtrim(rtrim(number_format($taxPercent, 2), '0'), '.'),
                'taxAmount'     => $taxAmount,
                'total'         => $total,
                'timeline'      => $timeline,
                'nextLabel'     => $next ? 'Mark as ' . ucfirst($next) : null,
                'canCancel'     => in_array($root->status, ['pending', 'confirmed']),
            ]);
        }
    }
    public function storeOrderDieIn(Request $request)
    {
        $request->validate([
            'pickup_date' => 'required|date_format:d/m/Y',
            'pickup_time' => 'required',
            'phone'       => 'required|regex:/^[0-9]{7,12}$/',
            'order_type'  => 'required|in:dine_in', // force dine-in here
        ]);

        $cart      = session('cart', []);
        $userId    = session('guest_user_id');
        $table     = session('dine_in_table');   // you stored the full table model in session
        $tableId   = $table?->id;

        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Your cart is empty.'], 422);
        }

        // Build daily order number (ddmmyy + 5-digit seq)
        $lastToday = Order::whereDate('created_at', Carbon::today())->orderByDesc('id')->first();
        $seq       = $lastToday ? (int)substr($lastToday->order_no, -5) + 1 : 1;
        $orderNo   = Carbon::now()->format('dmy') . str_pad($seq, 5, '0', STR_PAD_LEFT);

        $pickupDate = Carbon::createFromFormat('d/m/Y', $request->pickup_date)->format('Y-m-d');

        // Subtotal from session cart
        $subtotal = array_sum(array_map(
            fn($it) => (int)($it['price'] ?? 0) * (int)($it['qty'] ?? 0),
            $cart
        ));

        // Taxes: apply ALL active
        $activeTaxes     = Tax::where('is_active', true)->orderByDesc('is_default')->orderBy('name')->get();
        $combinedPercent = (float)$activeTaxes->sum('percent');
        $taxAmount       = (int) round($subtotal * ($combinedPercent / 100));
        $total           = $subtotal + $taxAmount;
        $taxNames        = $activeTaxes->map(fn($t) => "{$t->name} {$t->percent}%")->join(' + ');

        $hasComment = collect($cart)->contains(fn($it) => filled(trim($it['comment'] ?? '')));

        DB::beginTransaction();
        try {
            $order = Order::create([
                'order_no'             => $orderNo,
                'user_id'              => $userId,
                'phone'                => $request->phone,
                'pickup_date'          => $pickupDate,
                'pickup_time'          => $request->pickup_time,
                'order_type'           => 'dine_in',
                'table_id'             => $tableId,
                'status'               => 'pending',

                'subtotal'             => $subtotal,
                'tax_amount'           => $taxAmount,
                'total'                => $total,
                'tax_name_snapshot'    => $taxNames,
                'tax_percent_snapshot' => $combinedPercent,

                'has_comment'          => $hasComment,
            ]);

            // Items + stock
            foreach ($cart as $productId => $item) {
                $product = Product::find($productId);
                if (!$product || $product->remain_qty < (int)$item['qty']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for: {$item['name']}"
                    ], 422);
                }

                $product->decrement('remain_qty', (int)$item['qty']);
                $product->increment('sell_qty',   (int)$item['qty']);

                $order->items()->create([
                    'product_id' => $productId,
                    'order_id'   => $order->id,
                    'name'       => $item['name'],
                    'price'      => (int)$item['price'],
                    'qty'        => (int)$item['qty'],
                    'comment'    => filled(trim($item['comment'] ?? '')) ? trim($item['comment']) : null,
                ]);
            }

            // Mark table unavailable
            if ($order->table_id) {
                Table::where('id', $order->table_id)->update(['status' => 'unavailable']);
            }

            // Status history
            $order->statusHistory()->create([
                'status'     => $order->status,
                'changed_at' => Carbon::now(),
            ]);

            // Clean up
            session()->forget('cart');
            DB::commit();

            event(new OrderCreated($order));

            return response()->json([
                'success'  => true,
                'message'  => 'Order placed successfully!',
                'order_no' => $orderNo,
                'totals'   => [
                    'subtotal'         => $subtotal,
                    'tax_amount'       => $taxAmount,
                    'total'            => $total,
                    'combined_percent' => $combinedPercent,
                    'taxes'            => $activeTaxes->map(fn($t) => [
                        'id' => $t->id,
                        'name' => $t->name,
                        'percent' => $t->percent
                    ])->values(),
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }
    public function storeAddOnOrder(Request $request)
    {
        $request->validate([
            'parent_order_id'   => 'nullable|exists:orders,id',
            'table_id'          => 'required|exists:tables,id',
            'cart'              => 'required|array|min:1',
            'cart.*.product_id' => 'required|exists:products,id',
            'cart.*.name'       => 'required|string',
            'cart.*.price'      => 'required|numeric|min:0',
            'cart.*.qty'        => 'required|integer|min:1',
            // optional: front-end can pass snapshots; we still recalc on server
            'subtotal'          => 'nullable|numeric|min:0',
            'tax_amount'        => 'nullable|numeric|min:0',
            'tax_percent'       => 'nullable|numeric|min:0',
            'total'             => 'nullable|numeric|min:0',
            'tax_snapshot'      => 'nullable|string',
        ]);

        $cart     = $request->input('cart', []);
        $table_id = (int) $request->table_id;

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty.'
            ], 422);
        }

        // ---- Compute subtotal from payload (server-trust) ----
        $subtotal = array_reduce($cart, function ($sum, $it) {
            return $sum + ((int)$it['price'] * (int)$it['qty']);
        }, 0);

        // ---- Active taxes (server-of-record) ----
        $activeTaxes     = \App\Models\Tax::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'percent']);
        $combinedPercent = (float) $activeTaxes->sum('percent');
        $taxAmount       = (int) round($subtotal * ($combinedPercent / 100));
        $grandTotal      = (int) ($subtotal + $taxAmount);
        $taxNamesSnapshot = $activeTaxes->map(fn($t) => "{$t->name} {$t->percent}%")->join(' + ');

        // ---- Order no (date + 5-digit sequence) ----
        $today    = \Carbon\Carbon::now();
        $datePart = $today->format('dmy');
        $last     = \App\Models\Order::whereDate('created_at', \Carbon\Carbon::today())->latest('id')->first();
        $nextSeq  = $last ? str_pad(((int)substr($last->order_no, -5)) + 1, 5, '0', STR_PAD_LEFT) : '00001';
        $orderNo  = $datePart . $nextSeq;

        // ---- Determine user / parent / pickup ----
        $parentOrder = null;
        $user_id     = null;
        $orderType   = 'dine_in';
        $phone       = null;
        $hasComment = collect($cart)->contains(fn($it) => filled(trim($it['comment'] ?? '')));

        if ($request->filled('parent_order_id')) {
            $parentOrder = \App\Models\Order::findOrFail($request->parent_order_id);
            $user_id     = $parentOrder->user_id;
            $orderType   = $parentOrder->order_type;
            $phone       = $parentOrder->phone;
            $pickupDT = \Carbon\Carbon::parse($parentOrder->pickup_date)->setTimeFromTimeString($parentOrder->pickup_time)
                ->addMinutes(20);
            $pickupDate = $pickupDT->toDateString();
            $pickupTime = $pickupDT->format('H:i:s');
        } else {
            $user = \App\Models\User::create([
                'name'     => 'Guest_' . \Illuminate\Support\Str::random(5),
                'email'    => \Illuminate\Support\Str::uuid() . '@guest.local',
                'password' => bcrypt('customer'),
                'role'     => 'customer',
            ]);
            $user_id  = $user->id;
            $pickupDT = \Carbon\Carbon::now()->addMinutes(20);
        }

        $pickupDate = $pickupDT->format('Y-m-d');
        $pickupTime = $pickupDT->format('H:i:s');

        DB::beginTransaction();
        try {
            // ---- Create order (with tax snapshots) ----
            $order = \App\Models\Order::create([
                'order_no'             => $orderNo,
                'user_id'              => $user_id,
                'phone'                => $phone,
                'pickup_date'          => $pickupDate,
                'pickup_time'          => $pickupTime,
                'order_type'           => $orderType,
                'table_id'             => $table_id,
                'status'               => 'pending',

                // snapshots + totals
                'subtotal'             => $subtotal,
                'tax_amount'           => $taxAmount,
                'total'                => $grandTotal,
                'tax_name_snapshot'    => $taxNamesSnapshot,
                'tax_percent_snapshot' => $combinedPercent,

                // parent link (if any)
                'parent_order_id'      => $parentOrder?->id,
                'has_comment'          => $hasComment,
            ]);

            // ---- Items / stock ----
            foreach ($cart as $item) {
                /** @var \App\Models\Product|null $product */
                $product = \App\Models\Product::find($item['product_id']);

                if (!$product || $product->remain_qty < (int)$item['qty']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for: {$item['name']}"
                    ], 422);
                }

                $product->decrement('remain_qty', (int)$item['qty']);
                $product->increment('sell_qty',   (int)$item['qty']);

                $order->items()->create([
                    'product_id' => (int)$item['product_id'],
                    'order_id'   => $order->id,
                    'name'       => $item['name'],
                    'price'      => (int)$item['price'],
                    'qty'        => (int)$item['qty'],
                    'comment'    => filled(trim($item['comment'] ?? '')) ? trim($item['comment']) : null,
                ]);
            }

            // ---- History ----
            $order->statusHistory()->create([
                'status'     => $order->status,
                'changed_at' => \Carbon\Carbon::now(),
            ]);

            // ---- If it's an add-on, mark parent ----
            if ($parentOrder) {
                $parentOrder->update(['has_add_on' => true]);
            }

            DB::commit();

            event(new \App\Events\OrderCreated($order));

            return response()->json([
                'success'            => true,
                'message'            => $parentOrder ? 'Add-On Order placed successfully!' : 'Order placed successfully!',
                'order_no'           => $orderNo,
                'subtotal'           => $subtotal,
                'tax_amount'         => $taxAmount,
                'total'              => $grandTotal,
                'tax_percent'        => $combinedPercent,
                'tax_name_snapshot'  => $taxNamesSnapshot,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while creating the order.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function orderDetail($id)
    {
        // Eager load related data to avoid N+1 queries
        $order = Order::with([
            'items.product',   // item.product for images & names
            'customer'         // optional, if you want customer info
        ])->findOrFail($id);

        // Optional: group timestamps or prepare for timeline
        // $statusTimestamps = $order->status_timestamps ?? [];
        $timestamps = [];
        foreach (['pending', 'confirmed', 'preparing', 'delivered', 'eating', 'done', 'canceled'] as $status) {
            $timestamps[$status] = optional(
                $order->statusHistory()->where('status', $status)->latest()->first()
            )->created_at;
        }



        return view('customer.take_away.order_details', [
            'order' => $order,
            'status_timestamps' => $timestamps,
        ]);
    }
    public function DieInOrderDetail($id)
    {
        // Eager load related data to avoid N+1 queries
        $order = Order::with([
            'items.product',   // item.product for images & names
            'customer'         // optional, if you want customer info
        ])->findOrFail($id);

        // Optional: group timestamps or prepare for timeline
        // $statusTimestamps = $order->status_timestamps ?? [];
        $timestamps = [];
        foreach (['pending', 'confirmed', 'preparing', 'delivered', 'eating', 'done', 'canceled'] as $status) {
            $timestamps[$status] = optional(
                $order->statusHistory()->where('status', $status)->latest()->first()
            )->created_at;
        }



        return view('customer.die_in.order_details', [
            'order' => $order,
            'status_timestamps' => $timestamps,
        ]);
    }

    // public function DieInOrderHistory()
    // {
    //     $userId = auth()->id() ?? session('guest_user_id');

    //     $orders = Order::with('items')
    //         ->where('user_id', $userId)
    //         ->where('order_type', 'dine_in')
    //         ->orderByDesc('created_at')
    //         ->get()
    //         ->groupBy(function ($order) {
    //             $created = Carbon::parse($order->created_at);
    //             if ($created->isToday()) return 'Today';
    //             elseif ($created->isYesterday()) return 'Yesterday';
    //             elseif ($created->greaterThan(Carbon::now()->subDays(7))) return 'Last Week';
    //             else return 'Older Orders';
    //         });
    //     return view('customer.die_in.order_history', compact('orders'));
    // }
    public function DieInOrderHistory()
    {
        $userId = auth()->id() ?? session('guest_user_id');

        $orders = Order::with(['items', 'parent'])   // ⬅️ add parent
            ->where('user_id', $userId)
            ->where('order_type', 'dine_in')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(function ($order) {
                $created = Carbon::parse($order->created_at);
                if ($created->isToday()) return 'Today';
                elseif ($created->isYesterday()) return 'Yesterday';
                elseif ($created->greaterThan(Carbon::now()->subDays(7))) return 'Last Week';
                else return 'Older Orders';
            });

        return view('customer.die_in.order_history', compact('orders'));
    }

    public function takeAwayOrderHistory()
    {
        $userId = auth()->id() ?? session('guest_user_id');
        $orders = Order::with('items')
            ->where('user_id', $userId)
            ->where('order_type', 'takeaway')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(function ($order) {
                $created = Carbon::parse($order->created_at);
                if ($created->isToday()) return 'Today';
                elseif ($created->isYesterday()) return 'Yesterday';
                elseif ($created->greaterThan(Carbon::now()->subDays(7))) return 'Last Week';
                else return 'Older Orders';
            });
        return view('customer.take_away.order_history', compact('orders'));
    }
    public function takeAwayOrderDetails()
    {
        return view('customer.take_away.order_details');
    }
    public function updateStatusAdmin(Request $request, Order $order)
    {
        $flow = [
            'pending'   => 'confirmed',
            'confirmed' => 'preparing',
            'preparing' => 'delivered',
            'delivered' => 'eating',
            'eating'    => 'done',
        ];

        // Allow explicit target via ?to=preparing, otherwise follow flow
        $to = $request->string('to')->lower()->value();

        if ($order->status === 'canceled' || $order->status === 'done') {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be changed.',
            ], 400);
        }

        if ($to) {
            // Optional: sanity check allowed values
            if (!in_array($to, array_keys($flow)) && $to !== 'done') {
                return response()->json(['success' => false, 'message' => 'Invalid status.'], 422);
            }
            $next = $to;
        } else {
            if (!array_key_exists($order->status, $flow)) {
                return response()->json(['success' => false, 'message' => 'No next status.'], 400);
            }
            $next = $flow[$order->status];
        }

        $order->updateStatus($next);


        return response()->json([
            'success' => true,
            'status'  => $order->status,
            'message' => 'Status updated',
        ]);
    }
    public function cancel(Request $request, Order $order)
    {
        if (!in_array($order->status, ['pending', 'confirmed'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending or confirmed orders can be canceled.',
            ], 400);
        }

        $order->updateStatus('canceled');

        return response()->json([
            'success' => true,
            'status'  => 'canceled',
            'message' => 'Order has been canceled.',
        ]);
    }
}
