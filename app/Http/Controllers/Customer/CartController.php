<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CartController extends Controller
{


    // add to cart session
    public function addAjax(Request $request)
    {
        $product = Product::findOrFail($request->product_id);
        $qty = (int) $request->qty;
        $userId = $request->user_id;

        $cart = session()->get('cart', []);
        $comment = trim((string) $request->comment ?? '');



        if (isset($cart[$product->id])) {
            $cart[$product->id]['qty'] += $qty;
            if ($comment !== '') {
                $cart[$product->id]['comment'] = Str::limit($comment, 500);
            }
        } else {
            $cart[$product->id] = [
                'name' => $product->name,
                'price' => $product->price,
                'qty' => $qty,
                'image' => $product->image,
                'user_id' => $userId,
                'comment'  => Str::limit($comment, 500), // NEW
            ];
        }

        session()->put('cart', $cart);

        return response()->json(['message' => 'Item added to cart']);
    }
    public function updateComment(Request $request)
    {
        $pid  = (string) $request->product_id;
        $text = Str::limit(trim((string) $request->comment), 500);

        $cart = session('cart', []);
        if (!isset($cart[$pid])) {
            return response()->json(['ok' => false, 'error' => 'Item not found'], 404);
        }

        $cart[$pid]['comment'] = $text;
        session(['cart' => $cart]);

        return response()->json(['ok' => true, 'comment' => $text]);
    }
    public function validateStock(Request $request)
    {
        $cart = session('cart', []);
        $shortages = [];

        foreach ($cart as $productId => $item) {
            $requested = (int)($item['qty'] ?? 0);
            $product   = Product::find($productId);
            $remain    = (int)($product->remain_qty ?? 0);

            if (!$product || $requested > $remain) {
                $shortages[] = [
                    'product_id' => $productId,
                    'name'       => $item['name'] ?? ($product->name ?? 'Unknown'),
                    'requested'  => $requested,
                    'remain'     => $remain,
                ];
            }
        }

        if ($shortages) {
            return response()->json([
                'ok'      => false,
                'message' => 'Some items exceed available stock.',
                'errors'  => $shortages,
            ], 422);
        }

        return response()->json(['ok' => true]);
    }
    // public function fetchCartHtml()
    // {
    //     $cart = session('cart', []);
    //     $html = '';

    //     if (empty($cart)) {
    //         return '<div class="text-center text-gray-500 py-10">Your cart is empty.</div>';
    //     }

    //     foreach ($cart as $id => $item) {
    //         $html .= '
    //         <div class="flex items-center bg-gray-50 rounded-xl p-2.5 mb-3.5 gap-3.5 flex-wrap sm:flex-nowrap shadow-sm">
    //             <img src="' . asset($item['image'] ?? 'assets/images/logo/logo.png') . '" class="w-20 h-20 object-cover rounded-lg">
    //             <div class="flex-grow w-full sm:w-auto">
    //                 <h4 class="text-base font-semibold mb-1">' . $item['name'] . '</h4>
    //                 <p class="text-gray-600 text-sm mb-2.5">' . number_format($item['price']) . ' MMK /a piece</p>
    //                 <div class="flex items-center gap-2.5 rounded-full py-1 px-2.5">
    //                     <button onclick="updateQuantity(' . $id . ', 1)" class="bg-green-500 text-white w-6 h-6 rounded-full text-xs flex items-center justify-center">+</button>
    //                     <span class="text-base font-medium">' . $item['qty'] . '</span>
    //                     <button onclick="updateQuantity(' . $id . ', -1)" class="bg-red-500 text-white w-6 h-6 rounded-full text-xs flex items-center justify-center">−</button>
    //                 </div>
    //             </div>

    //             <button onclick="removeItem(' . $id . ')" class="text-xl text-green-700 cursor-pointer hover:text-red-600 transition-colors duration-200">
    //                 <i class="fa-solid fa-trash" style="color: #ea1d06;"></i>
    //             </button>
    //         </div>';
    //     }

    //     return response($html);
    // }
    public function fetchCartHtml()
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return '<div class="text-center text-gray-500 py-10">Your cart is empty.</div>';
        }

        $html = '';

        foreach ($cart as $id => $item) {
            $pid      = (string) $id;
            $name     = e($item['name'] ?? '—');
            $priceRaw = (int) ($item['price'] ?? 0);
            $price    = number_format($priceRaw);
            $qty      = max(1, (int) ($item['qty'] ?? 1));
            $image    = asset($item['image'] ?? 'assets/images/logo/logo.png');
            $comment  = trim((string) ($item['comment'] ?? ''));
            $commentEsc = e($comment);
            $subtotal = number_format($priceRaw * $qty);

            $minusDisabledClass = $qty <= 1 ? 'opacity-40 cursor-not-allowed' : '';
            $minusDisabledAttr  = $qty <= 1 ? 'disabled' : '';

            $html .= '
        <div class="group relative rounded-2xl border border-gray-100 bg-white p-3 sm:p-4 shadow-sm hover:shadow-md transition mb-3.5">
          <!-- Remove -->



          <div class="grid grid-cols-[72px_1fr_auto] sm:grid-cols-[96px_1fr_auto] items-start gap-3 sm:gap-4">
            <!-- Image -->
            <img
              src="' . $image . '"
              onerror="this.src=\'' . asset('assets/images/logo/logo.png') . '\';"
              alt="Product"
              class="w-18 h-18 sm:w-24 sm:h-24 rounded-lg object-cover"
            />

            <!-- Content -->
            <div class="min-w-0">
              <h4 class="text-base sm:text-lg font-semibold text-gray-900 truncate">' . $name . '</h4>
              <p class="text-gray-500 text-sm">' . $price . ' MMK <span class="text-gray-400">/ piece</span></p>

              <!-- Qty -->
              <div class="mt-2 sm:mt-3 flex items-center gap-3">
                <button onclick="updateQuantity(\'' . $pid . '\', -1)"
                        class="h-9 w-9 rounded-full border border-gray-200 text-lg bg-red-500 leading-none flex items-center justify-center  ' . $minusDisabledClass . '"
                        ' . $minusDisabledAttr . ' aria-label="Decrease">−</button>

                <span class="min-w-8 text-center text-base font-semibold">' . $qty . '</span>

                <button onclick="updateQuantity(\'' . $pid . '\', 1)"
                        class="h-9 w-9 rounded-full border border-gray-200 text-lg leading-none flex items-center justify-center bg-green-500"
                        aria-label="Increase">+</button>
              </div>

              <!-- Note UI -->
              <div class="mt-3">
                <div id="note-view-' . $pid . '" class="' . ($comment === '' ? 'hidden' : 'flex') . ' items-start justify-between gap-3">
                  <div class="text-sm text-gray-700 max-w-[28rem] break-words">Customer Note : ' . nl2br($commentEsc) . '</div>
                  <button onclick="showNoteEditor(\'' . $pid . '\')" class="text-xs text-green-700 hover:underline shrink-0">Edit</button>
                </div>

                <div id="note-empty-' . $pid . '" class="' . ($comment === '' ? '' : 'hidden') . '">
                  <button onclick="showNoteEditor(\'' . $pid . '\')" class="text-xs text-green-700 hover:underline">Add note</button>
                </div>

                <div id="note-edit-' . $pid . '" class="hidden mt-2">
                  <textarea id="note-input-' . $pid . '"
                            class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                            rows="2" placeholder="Add a note for this item…">' . $commentEsc . '</textarea>
                  <div class="mt-2 flex gap-2">
                    <button id="save-note-btn-' . $pid . '" onclick="saveNote(\'' . $pid . '\')"
                            class="px-3 py-1.5 rounded-full bg-green-700 text-white text-sm font-semibold hover:bg-green-800 disabled:opacity-60">Save</button>
                    <button onclick="cancelEditNote(\'' . $pid . '\')"
                            class="px-3 py-1.5 rounded-full border border-gray-300 text-sm font-semibold hover:bg-gray-50">Cancel</button>
                  </div>
                  <div id="note-hint-' . $pid . '" class="mt-1 text-xs text-gray-500"></div>
                </div>
              </div>
            </div>

            <!-- Subtotal -->
            <div class="text-right">
             <button
    onclick="removeItem(\'' . $pid . '\')"
    class="  p-2
            text-red-600 hover:bg-white focus:outline-none focus:ring-2 focus:ring-red-500 transition"
    aria-label="Remove item" title="Remove"
    >
    <i class="fa-solid fa-trash"></i>
    </button>
              <div class="text-xs text-gray-500">Subtotal</div>
              <div class="text-lg font-semibold text-gray-900 whitespace-nowrap">' . $subtotal . ' MMK</div>
            </div>
          </div>
        </div>';
        }

        return response($html);
    }
    public function setCartTax(Request $request)
    {
        $request->validate([
            'tax_id' => ['nullable', 'integer', 'exists:taxes,id'],
        ]);

        $tax = null;
        if ($request->filled('tax_id')) {
            $tax = Tax::where('is_active', true)->find($request->tax_id);
        }

        if ($tax) {
            session(['selected_tax_id' => $tax->id]);
            return response()->json(['ok' => true, 'tax_id' => $tax->id]);
        }

        // clear (fallback to default later)
        session()->forget('selected_tax_id');
        return response()->json(['ok' => true, 'tax_id' => null]);
    }



    // public function fetchCartTotal()
    // {
    //     $cart = session('cart', []);
    //     $total = array_sum(array_map(fn($item) => $item['price'] * $item['qty'], $cart));
    //     return response()->json(['total' => number_format($total) . ' MMK']);
    // }
    public function fetchCartTotal(Request $request)
    {
        $cart = session('cart', []);

        $subtotal = array_sum(array_map(
            fn($it) => (int)($it['price'] ?? 0) * (int)($it['qty'] ?? 0),
            $cart
        ));

        // Apply ALL active taxes
        $taxes = Tax::where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $combinedPercent = (float) $taxes->sum('percent');
        $taxAmount       = (int) floor($subtotal * ($combinedPercent / 100));
        $total           = $subtotal + $taxAmount;

        return response()->json([
            'subtotal'         => $subtotal,
            'tax_amount'       => $taxAmount,
            'total'            => $total,
            'combined_percent' => (float) number_format($combinedPercent, 2, '.', ''),
            'taxes'            => $taxes->map(fn($t) => [
                'id'      => $t->id,
                'name'    => $t->name,
                'percent' => (float) number_format($t->percent, 2, '.', ''),
            ])->values(),
        ]);
    }

    public function updateAjax(Request $request)
    {
        $productId = $request->product_id;
        $change = (int) $request->change;

        $cart = session()->get('cart', []);
        if (isset($cart[$productId])) {
            $cart[$productId]['qty'] += $change;
            if ($cart[$productId]['qty'] <= 0) {
                unset($cart[$productId]);
            }
            session()->put('cart', $cart);
        }

        // Optionally return updated total
        $totalQty = array_sum(array_column($cart, 'qty'));
        $totalPrice = array_sum(array_map(fn($item) => $item['qty'] * $item['price'], $cart));

        return response()->json([
            'item_count' => $totalQty,
            'cart_total' => number_format($totalPrice) . ' MMK'
        ]);
    }


    public function removeAjax(Request $request)
    {
        $cart = session()->get('cart', []);
        $productId = $request->input('product_id');

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
        }

        return response()->json(['success' => true]);
    }


    public function takeAwayCart()
    {
        $cart = session('cart', []);
        return view('customer.take_away.cart', compact('cart'));
    }
    public function dieInCart()
    {
        $cart = session('cart', []);
        return view('customer.die_in.cart', compact('cart'));
    }
    public function takeAwayCheckout()
    {
        return view('customer.take_away.take_away_checkout');
    }
    public function counts()
    {
        $cart = session('cart', []);
        // distinct product_id count (badge) and total qty if you ever need it
        $distinct = count($cart);
        $totalQty = collect($cart)->sum('qty');

        return response()->json([
            'distinct' => $distinct,
            'totalQty' => $totalQty,
        ]);
    }
}
