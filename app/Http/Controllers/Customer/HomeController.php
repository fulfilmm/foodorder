<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    public function index()
    {
        return view('customer.index');
    }
    public function scanner()
    {
        return view('customer.qr_scanner');
    }
    // public function checkTableName(Request $request)
    // {
    //     $tableName = $request->query('table');

    //     $table = Table::where('name', $tableName)->first();

    //     if (!$table) {
    //         return redirect()->route('customer.die_in.scanner')->with('error', 'Invalid table QR. Try again.');
    //     }

    //     // Store in session (or other state mechanism)
    //     session(['dine_in_table' => $table]);

    //     return redirect()->route('customer.die_in.home');
    // }


    public function checkTableName(Request $request)
    {
        $tableName = $request->query('table'); // QR payload (name). Switch to code/id if needed.

        // 1) Resolve table
        $table = Table::where('name', $tableName)->first();
        if (!$table) {
            return redirect()
                ->route('customer.die_in.scanner')
                ->with('error', 'Invalid table QR. Try again.');
        }

        // 2) Who is scanning?
        $userId = Auth::id() ?? session('guest_user_id');

        // 3) Get the latest DINE-IN order for this table
        //    (If you want to consider today only, uncomment the whereDate line)
        $latest = Order::where('table_id', $table->id)
            ->where('order_type', 'dine_in')
            ->whereDate('created_at', Carbon::today())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        // 4) Check status gate
        if ($latest) {
            $status = strtolower(trim($latest->status));
            $terminal = ['done',  'canceled'];

            $isTerminal   = in_array($status, $terminal);
            $isOwnOrder   = ($latest->user_id === $userId);

            // Block only if there is an active (non-terminal) latest order that belongs to someone else
            // if (!$isTerminal && !$isOwnOrder) {
            //     return redirect()
            //         ->route('customer.die_in.scanner')
            //         ->with(
            //             'error',
            //             "{$table->name} is currently in use (Order #{$latest->order_no} — " . ucfirst($latest->status) . ")."
            //         );
            // }
            if (!$isTerminal && !$isOwnOrder) {
                return redirect()
                    ->route('customer.die_in.scanner')
                    ->with(
                        'error',
                        "{$table->name} is currently in use by another user"
                    );
            }
        }

        // 5) Allow scan: store only IDs in session
        session(['dine_in_table' => $table]);

        return redirect()
            ->route('customer.die_in.home')
            ->with('success', "Welcome to table {$table->name}.");
    }

    // get all
    // public function takeAwayHome(Request $request)
    // {
    //     $categories = Category::all();
    //     $selectedCategory = $request->query('category');

    //     $products = Product::with('category')->get();
    //     if (!Session::has('guest_user_id')) {
    //         // Create new guest user in DB
    //         $user = \App\Models\User::create([
    //             'name' => 'Guest_' . Str::random(5),
    //             'email' => Str::uuid() . '@guest.local',
    //             // 'password' => bcrypt(Str::random(10)),
    //             'password' => bcrypt('customer'),
    //             'role' => 'customer',
    //         ]);

    //         // Store guest user ID in session
    //         Session::put('guest_user_id', $user->id);
    //     }

    //     return view('customer.take_away.home', compact('categories', 'products', 'selectedCategory'));
    // }
    // public function takeAwayHome(Request $request)
    // {
    //     // ensure guest user
    //     if (! Session::has('guest_user_id')) {
    //         $user = \App\Models\User::create([
    //             'name'  => 'Guest_'.Str::random(5),
    //             'email' => Str::uuid().'@guest.local',
    //             'password' => bcrypt('customer'),
    //             'role'  => 'customer',
    //         ]);
    //         Session::put('guest_user_id', $user->id);
    //     }

    //     // server-render page 1 with pagination
    //     $categories = Category::all();
    //     $selectedCategory = $request->query('category', '');
    //     $search = $request->query('search', '');

    //     $q = Product::with('category');
    //     if (! $search && $selectedCategory) {
    //         $q->whereHas('category', fn($qb) =>
    //             $qb->where('name', $selectedCategory)
    //         );
    //     }
    //     if ($search) {
    //         $q->where(fn($qb) =>
    //             $qb->where('name','like',"%{$search}%")
    //                ->orWhere('code','like',"%{$search}%")
    //                ->orWhereHas('category', fn($q2)=>
    //                    $q2->where('name','like',"%{$search}%")
    //                )
    //         );
    //     }

    //     // paginate 20 per page
    //     $products = $q->paginate(20);

    //     // pull out the product_ids already in session
    //     $sessionIds = array_keys(session('cart', []));

    //     return view('customer.take_away.home', [
    //         'categories'      => $categories,
    //         'products'        => $products,          // Paginator!
    //         'selectedCategory'=> $selectedCategory,
    //         'search'          => $search,
    //         'sessionIds'      => $sessionIds,
    //     ]);
    // }
    public function takeAwayHome(Request $request)
    {
        // ensure guest user
        // if (Session::has('guest_user_id')) {
        //     $user = \App\Models\User::create([
        //         'name'     => 'Guest_' . Str::random(5),
        //         'email'    => Str::uuid() . '@guest.local',
        //         'password' => bcrypt('customer'),
        //         'role'     => 'customer',
        //     ]);
        //     Session::put('guest_user_id', $user->id);
        // }
        // Ensure a guest customer exists and is authenticated
        if (!Session::has('guest_user_id')) {
            $user = \App\Models\User::create([
                'name'              => 'Guest_' . Str::upper(Str::random(5)),
                'email'             => Str::uuid() . '@guest.local', // unique and non-deliverable
                'password'          => Hash::make('customer'),
                'role'              => 'customer',
                'email_verified_at' => now(), // optional if you gate features on verification
            ]);
            Session::put('guest_user_id', $user->id);
            Auth::login($user, true); // remember=true
        } else {
            $user = \App\Models\User::find(Session::get('guest_user_id'));

            // If missing (deleted?) recreate; else ensure they’re logged in
            if (!$user) {
                Session::forget('guest_user_id');
                $user = \App\Models\User::create([
                    'name'              => 'Guest_' . Str::upper(Str::random(5)),
                    'email'             => Str::uuid() . '@guest.local',
                    'password'          => Hash::make('customer'),
                    'role'              => 'customer',
                    'email_verified_at' => now(),
                ]);
                Session::put('guest_user_id', $user->id);
                Auth::login($user, true);
            } elseif (!Auth::check() || Auth::id() !== $user->id) {
                Auth::login($user, true);
            }
        }

        $categories       = Category::all();
        $selectedCategory = $request->query('category', '');
        $search           = $request->query('search', '');

        $q = Product::with('category:id,name');

        // only filter by category when NOT searching
        if (!$search && $selectedCategory) {
            $q->whereHas('category', fn($qb) => $qb->where('name', $selectedCategory));
        }

        // global search
        if ($search) {
            $q->where(
                fn($qb) =>
                $qb->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('category', fn($qb2) => $qb2->where('name', 'like', "%{$search}%"))
            );
        }

        // ⬅️ Latest first (updated_at, then id)
        $q->orderByDesc('updated_at')->orderByDesc('id');

        $products   = $q->paginate(20);   // page 1 for SSR
        $sessionIds = array_keys(session('cart', []));

        return view('customer.take_away.home', [
            'categories'       => $categories,
            'products'         => $products,
            'selectedCategory' => $selectedCategory,
            'search'           => $search,
            'sessionIds'       => $sessionIds,
        ]);
    }
    public function ajaxProducts1(Request $request)
    {
        $categoryName = $request->query('category');
        $page = $request->query('page', 1);

        $products = Product::with('category')
            ->whereHas('category', function ($query) use ($categoryName) {
                $query->where('name', $categoryName);
            })
            ->paginate(20);

        $html = '';
        foreach ($products as $index => $product) {
            $html .= '
            <div class="bg-white p-3 rounded-xl shadow-sm hover:shadow-md transition duration-300 flex flex-col items-center space-y-2 h-fit">
              <img src="' . asset($product->image ?? 'assets/images/logo/logo.png') . '" class="rounded-md w-full h-28 object-cover hover:scale-105 transition-transform duration-300" />
              <div class="text-center space-y-1">
              <h4 class="font-bold text-sm text-gray-800">' . e($product->name) . '</h4>
              <h4 class="font-bold text-xs text-gray-800">' . e($product->code) . '</h4>
              <p class="text-green-600 font-semibold text-xs">' . number_format($product->price) . ' MMK</p>
              <p class="text-green-600 font-semibold text-xs">' . e($product->category->name) . ' </p>
               <p class="text-xs text-gray-500 leading-snug">' . e($product->description) . ' MMK</p>
              </div>


              <div class="counter hidden flex items-center justify-between bg-gray-100 rounded-full py-1 px-3 w-28 mx-auto mb-2" id="counter-' . $index . '">
                  <button onclick="updateQuantity(' . $index . ', -1, ' . $product->price . ')" class="text-lg text-green-700">−</button>
                  <span id="qty-' . $index . '" class="font-bold text-green-700">1</span>
                  <button onclick="updateQuantity(' . $index . ', 1, ' . $product->price . ')" class="text-lg text-green-700">+</button>
              </div>
              <button id="btn-' . $index . '" data-product-id="' . $product->id . '" onclick="addToCart(' . $product->price . ', ' . $index . ')" class="btn bg-green-700 text-white py-2 px-4 rounded-full w-full font-bold mt-2">
                  Add to Cart
              </button>
            </div>';
        }

        return response($html);
    }
    public function ajaxProducts(Request $request)
    {
        $categoryName = $request->query('category');
        $search       = $request->query('search');
        $page         = (int) $request->query('page', 1);

        $q = Product::with('category:id,name');

        if (!$search && $categoryName) {
            $q->whereHas('category', fn($qb) => $qb->where('name', $categoryName));
        }

        if ($search) {
            $q->where(
                fn($qb) =>
                $qb->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('category', fn($qb2) => $qb2->where('name', 'like', "%{$search}%"))
            );
        }

        // ⬅️ Latest first
        $q->orderByDesc('updated_at')->orderByDesc('id');

        $products = $q->paginate(20, ['*'], 'page', $page);

        $html = '';
        foreach ($products as $p) {
            $pid  = (int) $p->id;
            $img  = asset($p->image ?: 'assets/images/logo/logo.png');

            // pricing (supports discount fields if present)
            $actual      = (int) ($p->actual_price ?? $p->price ?? 0);
            $hasDiscount = (bool) ($p->has_discount ?? false);
            $type        = $p->discount_type ?? null;   // 'percent' | 'fixed' | null
            $val         = is_null($p->discount_value) ? null : (int) $p->discount_value;

            $discountAmt = 0;
            if ($hasDiscount && $val !== null) {
                if ($type === 'percent') {
                    $percent    = max(0, min(100, $val));
                    $discountAmt = (int) floor($actual * $percent / 100);
                } elseif ($type === 'fixed') {
                    $discountAmt = min($actual, max(0, $val));
                }
            }
            $final      = (int) ($p->price ?? max(0, $actual - $discountAmt));
            $isDiscount = $hasDiscount && $discountAmt > 0 && $final < $actual;

            $badge = '';
            if ($isDiscount) {
                $badge = $type === 'percent'
                    ? '<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-[11px] font-semibold">-' . (int)$val . '%</span>'
                    : '<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-[11px] font-semibold">-' . number_format($discountAmt) . ' MMK</span>';
            }

            $category = e(optional($p->category)->name ?? '—');
            $name     = e($p->name);
            $code     = e($p->code);
            $desc     = e($p->description);
            $out      = ((int) $p->remain_qty) <= 0;

            $html .= '
                    <div class="relative bg-white p-3 rounded-xl shadow-sm hover:shadow-md transition flex flex-col items-center space-y-2">
                        ' . ($isDiscount ? '<div class="absolute left-2 top-2 inline-flex items-center px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[10px] font-bold">SALE</div>' : '') . '
                        ' . ($out ? '<div class="absolute inset-0 bg-white/70 backdrop-blur-[1px] rounded-xl flex items-center justify-center text-gray-700 text-sm font-bold">Out of stock</div>' : '') . '

                        <img src="' . $img . '" alt="' . $name . '" loading="lazy" class="rounded-md w-full h-28 object-cover hover:scale-105 transition-transform"/>

                        <div class="text-center space-y-1">
                        <h4 class="font-bold text-sm text-gray-800">' . $name . '</h4>
                        <div class="text-[11px] text-gray-500">' . $code . ' • <span class="inline-block px-2 py-0.5 bg-gray-100 rounded-full">' . $category . '</span></div>';

            if ($isDiscount) {
                $html .= '
                        <div class="mt-1">
                            <span class="text-green-700 font-extrabold text-sm">' . number_format($final) . ' MMK</span>
                            <span class="text-gray-400 line-through text-xs ml-1">' . number_format($actual) . ' MMK</span>' . $badge . '
                        </div>';
            } else {
                $html .= '
                <div class="mt-1">
                    <span class="text-green-700 font-extrabold text-sm">' . number_format($final) . ' MMK</span>
                </div>';
            }

            $html .= '
                <p class="text-[11px] text-gray-500">' . $desc . '</p>
                </div>

                <div id="counter-' . $pid . '" class="counter hidden flex items-center justify-between bg-gray-100 rounded-full py-1 px-3 w-28">
                <button onclick="updateQuantity(' . $pid . ', -1, ' . $final . ')" class="text-lg text-green-700">−</button>
                <span id="qty-' . $pid . '" class="font-bold text-green-700">1</span>
                <button onclick="updateQuantity(' . $pid . ',  1, ' . $final . ')" class="text-lg text-green-700">+</button>
                </div>

                <button id="btn-' . $pid . '" data-product-id="' . $pid . '" ' . ($out ? 'disabled' : '') . '
                        onclick="addToCart(' . $final . ', ' . $pid . ')"
                        class="bg-green-700 text-white py-2 px-4 rounded-full w-full font-bold disabled:bg-gray-300 disabled:cursor-not-allowed">
                ' . ($out ? 'Out of Stock' : 'Add to Cart') . '
                </button>
            </div>';
        }

        // add no-cache headers so the browser doesn’t reuse old HTML
        return response($html)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }



    // public function ajaxProductsWaiter(Request $request)
    // {
    //     $categoryName = $request->query('category', 'all');
    //     $search       = $request->query('search');
    //     $page         = (int) $request->query('page', 1);

    //     $q = Product::with('category');

    //     if ($categoryName !== 'all') {
    //         $q->whereHas('category', function ($query) use ($categoryName) {
    //             $query->where('name', $categoryName);
    //         });
    //     }

    //     if ($search) {
    //         $s = trim($search);
    //         $q->where(function ($qb) use ($s) {
    //             $qb->where('name', 'like', "%{$s}%")
    //                 ->orWhere('code', 'like', "%{$s}%")
    //                 ->orWhereHas('category', function ($qb2) use ($s) {
    //                     $qb2->where('name', 'like', "%{$s}%");
    //                 });
    //         });
    //     }

    //     $products = $q->orderByDesc('updated_at')->orderByDesc('id')
    //         ->paginate(20, ['*'], 'page', $page);

    //     $html = '';
    //     foreach ($products as $product) {
    //         $productName = addslashes($product->name);
    //         $productImage = asset($product->image ?? 'assets/images/logo/logo.png');
    //         $productImageEscaped = addslashes($productImage);

    //         $html .= '
    //     <div class="bg-white p-3 rounded-xl shadow-sm hover:shadow-md transition duration-300 flex flex-col items-center space-y-2 h-fit">
    //         <img src="' . $productImage . '" alt="' . e($product->name) . '"
    //              class="rounded-md w-full h-28 object-cover hover:scale-105 transition-transform duration-300"/>
    //         <div class="text-center space-y-1">
    //             <h4 class="font-bold text-sm text-gray-800">' . e($product->name) . '</h4>
    //             <p class="text-green-600 font-semibold text-xs">' . number_format($product->price) . ' MMK</p>
    //             <p class="text-xs text-gray-500 leading-snug">' . e($product->description) . '</p>
    //         </div>

    //         <button
    //             onclick="addToCart(' . $product->id . ', \'' . $productName . '\', ' . (int)$product->price . ', \'' . $productImageEscaped . '\')"
    //             class="mt-auto bg-green-600 text-white px-3 py-1.5 rounded-full text-xs font-semibold hover:bg-green-700 transition w-full">
    //             Add to Cart
    //         </button>
    //     </div>';
    //     }

    //     return response($html)
    //         ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
    //         ->header('Pragma', 'no-cache');
    // }
    public function ajaxProductsWaiter(Request $request)
    {
        $categoryName = $request->query('category', 'all');
        $search       = $request->query('search');
        $page         = (int) $request->query('page', 1);

        $q = Product::with('category:id,name');

        if ($categoryName !== 'all') {
            $q->whereHas('category', function ($query) use ($categoryName) {
                $query->where('name', $categoryName);
            });
        }

        if ($search) {
            $s = trim($search);
            $q->where(function ($qb) use ($s) {
                $qb->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhereHas('category', function ($qb2) use ($s) {
                        $qb2->where('name', 'like', "%{$s}%");
                    });
            });
        }

        $products = $q->orderByDesc('updated_at')->orderByDesc('id')
            ->paginate(20, ['*'], 'page', $page);

        $html = '';
        foreach ($products as $p) {
            $pid = (int) $p->id;

            // image + safe strings
            $imgUrl   = asset($p->image ?: 'assets/images/logo/logo.png');
            $imgAttr  = addslashes($imgUrl);
            $nameHtml = e($p->name);
            $nameAttr = addslashes($p->name);
            $codeHtml = e($p->code);
            $descHtml = e($p->description);
            $catHtml  = e(optional($p->category)->name ?? '—');

            // stock
            $out = ((int) ($p->remain_qty ?? 1)) <= 0;

            // -------- Pricing with discount support --------
            $actual      = (int) ($p->actual_price ?? $p->price ?? 0); // original/base
            $hasDiscount = (bool) ($p->has_discount ?? false);
            $type        = $p->discount_type ?? null;                  // 'percent' | 'fixed' | null
            $val         = is_null($p->discount_value) ? null : (int) $p->discount_value;

            $discountAmt = 0;
            if ($hasDiscount && $val !== null) {
                if ($type === 'percent') {
                    $percent     = max(0, min(100, $val));
                    $discountAmt = (int) floor($actual * $percent / 100);
                } elseif ($type === 'fixed') {
                    $discountAmt = min($actual, max(0, $val));
                }
            }

            // final price (prefer explicit price column if set)
            $final      = (int) ($p->price ?? max(0, $actual - $discountAmt));
            $isDiscount = $hasDiscount && $discountAmt > 0 && $final < $actual;

            $badge = '';
            if ($isDiscount) {
                $badge = $type === 'percent'
                    ? '<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-[11px] font-semibold">-' . (int)$val . '%</span>'
                    : '<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-[11px] font-semibold">-' . number_format($discountAmt) . ' MMK</span>';
            }

            // -------- Card HTML --------
            $html .= '
<div class="relative bg-white p-3 rounded-xl shadow-sm hover:shadow-md transition flex flex-col items-center space-y-2 h-fit">
  ' . ($isDiscount ? '<div class="absolute left-2 top-2 inline-flex items-center px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[10px] font-bold">SALE</div>' : '') . '
  ' . ($out ? '<div class="absolute inset-0 bg-white/70 backdrop-blur-[1px] rounded-xl flex items-center justify-center text-gray-700 text-sm font-bold">Out of stock</div>' : '') . '

  <img src="' . $imgUrl . '" alt="' . $nameHtml . '" class="rounded-md w-full h-28 object-cover hover:scale-105 transition-transform"/>

  <div class="text-center space-y-1">
    <h4 class="font-bold text-sm text-gray-800">' . $nameHtml . '</h4>
    <div class="text-[11px] text-gray-500">' . $codeHtml . ' • <span class="inline-block px-2 py-0.5 bg-gray-100 rounded-full">' . $catHtml . '</span></div>';

            if ($isDiscount) {
                $html .= '
    <div class="mt-1">
      <span class="text-green-700 font-extrabold text-sm">' . number_format($final) . ' MMK</span>
      <span class="text-gray-400 line-through text-xs ml-1">' . number_format($actual) . ' MMK</span>' . $badge . '
    </div>';
            } else {
                $html .= '
    <div class="mt-1">
      <span class="text-green-700 font-extrabold text-sm">' . number_format($final) . ' MMK</span>
    </div>';
            }

            $html .= '
    <p class="text-[11px] text-gray-500">' . $descHtml . '</p>
  </div>

  <button
    onclick="addToCart(' . $pid . ', \'' . $nameAttr . '\', ' . $final . ', \'' . $imgAttr . '\')"
    ' . ($out ? 'disabled' : '') . '
    class="mt-auto bg-green-600 text-white px-3 py-1.5 rounded-full text-xs font-semibold hover:bg-green-700 transition w-full disabled:bg-gray-300 disabled:cursor-not-allowed">
    ' . ($out ? 'Out of Stock' : 'Add to Cart') . '
  </button>
</div>';
        }

        return response($html)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }



    // public function dieInHome(Request $request)
    // {
    //     $table = session('dine_in_table');
    //     if (! $table) {
    //         return redirect()->route('customer.die_in.scanner');
    //     }
    //     if (!Session::has('guest_user_id')) {
    //         $user = \App\Models\User::create([
    //             'name'              => 'Guest_' . Str::upper(Str::random(5)),
    //             'email'             => Str::uuid() . '@guest.local', // unique and non-deliverable
    //             'password'          => Hash::make('customer'),
    //             'role'              => 'customer',
    //             'email_verified_at' => now(), // optional if you gate features on verification
    //         ]);
    //         Session::put('guest_user_id', $user->id);
    //         Auth::login($user, true); // remember=true
    //     } else {
    //         $user = \App\Models\User::find(Session::get('guest_user_id'));

    //         // If missing (deleted?) recreate; else ensure they’re logged in
    //         if (!$user) {
    //             Session::forget('guest_user_id');
    //             $user = \App\Models\User::create([
    //                 'name'              => 'Guest_' . Str::upper(Str::random(5)),
    //                 'email'             => Str::uuid() . '@guest.local',
    //                 'password'          => Hash::make('customer'),
    //                 'role'              => 'customer',
    //                 'email_verified_at' => now(),
    //             ]);
    //             Session::put('guest_user_id', $user->id);
    //             Auth::login($user, true);
    //         } elseif (!Auth::check() || Auth::id() !== $user->id) {
    //             Auth::login($user, true);
    //         }
    //     }

    //     $categories       = Category::all();
    //     $selectedCategory = $request->query('category', '');
    //     $search           = $request->query('search', '');

    //     $q = Product::with('category')->orderByDesc('id');

    //     if (!$search && $selectedCategory) {
    //         $q->whereHas('category', fn($qb) => $qb->where('name', $selectedCategory));
    //     }
    //     if ($search) {
    //         $q->where(function ($qb) use ($search) {
    //             $qb->where('name', 'like', "%{$search}%")
    //                 ->orWhere('code', 'like', "%{$search}%")
    //                 ->orWhereHas('category', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
    //         });
    //     }

    //     // ⬇️ return a paginator (gives you lastPage(), currentPage(), etc.)
    //     $products   = $q->paginate(20);
    //     $sessionIds = array_keys(session('cart', []));

    //     return view('customer.die_in.home', compact('categories', 'products', 'selectedCategory', 'table', 'sessionIds'));
    // }
    public function dieInHome(Request $request)
    {
        $tables = session('dine_in_table');
        if (!$tables) {
            return redirect()->route('customer.die_in.scanner');
        }

        $table = Table::find($tables->id);
        if (!$table) {
            session()->forget('dine_in_table');
            return redirect()->route('customer.die_in.scanner')->with('error', 'Table not found. Please rescan.');
        }

        // ---- Ensure guest user is authenticated ----
        if (!Session::has('guest_user_id')) {
            $user = User::create([
                'name'              => 'Guest_' . Str::upper(Str::random(5)),
                'email'             => Str::uuid() . '@guest.local',
                'password'          => Hash::make('customer'),
                'role'              => 'customer',
                'email_verified_at' => now(),
            ]);
            Session::put('guest_user_id', $user->id);
            Auth::login($user, true);
        } else {
            $user = User::find(Session::get('guest_user_id'));
            if (!$user) {
                Session::forget('guest_user_id');
                $user = User::create([
                    'name'              => 'Guest_' . Str::upper(Str::random(5)),
                    'email'             => Str::uuid() . '@guest.local',
                    'password'          => Hash::make('customer'),
                    'role'              => 'customer',
                    'email_verified_at' => now(),
                ]);
                Session::put('guest_user_id', $user->id);
                Auth::login($user, true);
            } elseif (!Auth::check() || Auth::id() !== $user->id) {
                Auth::login($user, true);
            }
        }

        $userId = Auth::id() ?? Session::get('guest_user_id');

        // ---- Compute latest order today for THIS user at THIS table ----
        $latestOrderToday = Order::where('table_id', $table->id)
            ->where('user_id', $userId)
            ->where('order_type', 'dine_in')
            ->whereDate('created_at', Carbon::today())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();

        // Accept "done" or "cancel"/"canceled" as completed
        $showChangeTable = $latestOrderToday == null || $latestOrderToday
            && in_array(strtolower($latestOrderToday->status), ['done', 'cancel', 'canceled']);
        // dd($showChangeTable);

        // ---- Products/Categories (your existing listing) ----
        $categories       = Category::all();
        $selectedCategory = $request->query('category', '');
        $search           = $request->query('search', '');

        $q = Product::with('category')->orderByDesc('id');

        if (!$search && $selectedCategory) {
            $q->whereHas('category', fn($qb) => $qb->where('name', $selectedCategory));
        }

        if ($search) {
            $q->where(function ($qb) use ($search) {
                $qb->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('category', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $products   = $q->paginate(20);
        $sessionIds = array_keys(session('cart', []));

        return view('customer.die_in.home', compact(
            'categories',
            'products',
            'selectedCategory',
            'table',
            'sessionIds',
            'latestOrderToday',
            'showChangeTable'
        ));
    }

    public function dineInEntry()
    {
        $table = session('dine_in_table');
        // dd($table);

        if ($table && Table::find($table->id)) {
            return redirect()->route('customer.die_in.home');
        }

        return redirect()->route('customer.die_in.scanner');
    }
    public function forgetTable()
    {
        session()->forget('dine_in_table');
        return redirect()->route('customer.die_in.scanner')->with('info', 'Please scan the new table QR.');
    }
}
