{{-- <!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('assets/images/logo/logo.png') }}" type="image/png">
    <title>Dining Option</title>
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap"
      rel="stylesheet"
    />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
      /* Custom scrollbar hiding (Tailwind doesn't have direct utilities for this across all browsers) */
      .no-scrollbar::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Edge (Chromium-based), Opera */
      }
      .no-scrollbar {
        -ms-overflow-style: none; /* IE and Edge */
        scrollbar-width: none; /* Firefox */
      }

      /* Styles for draggable tabs - direct Tailwind replacement is complex, keeping a small custom style */
      .tabs.active {
        cursor: grabbing;
      }

      /* Apply Inter font, as cdn.tailwindcss.com doesn't automatically load Google Fonts */
      body {
        font-family: "Inter", sans-serif;
      }
    </style>
  </head>
  <body class="bg-white p-4 h-full overflow-hidden">
    <nav class="fixed inset-x-0 top-0 z-50 bg-white border-b border-green-200">
      <div class="flex h-14 items-center justify-between px-4 sm:px-6">
        <div class="flex items-center space-x-3">
          <div class="h-9 w-9 rounded-full bg-white">
            <img src="{{asset('assets/images/logo/logo.png')}}" alt="" srcset="" />
          </div>
          <span class="text-2xl font-extrabold text-green-700">Hello!</span>
        </div>

        <div class="flex items-center space-x-6">
          <a
            href="{{route('customer.take_away.home')}}"
            class="flex items-center text-green-700 hover:text-green-600 transition-colors duration-200 active"
          >
            <svg
            class="h-6 w-6 fill-current"
            fill="none"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path
                d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"
              />
            </svg>
            <span class="hidden sm:inline ml-2">Home</span>
          </a>

          <a
            href="{{route('customer.take_away.order_history')}}"
            class="flex items-center hover:text-green-600 transition-colors duration-200"
          >
            <svg
              class="h-6 w-6 stroke-current"
              fill="none"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M4 4h16v16H4zm4 4h8m-8 4h8m-8 4h4"
              />
            </svg>
            <span class="hidden sm:inline ml-2">Orders</span>
          </a>

          <a
            href="#"
            class="flex items-center hover:text-green-600 transition-colors duration-200"
          >
            <svg
              class="h-6 w-6 stroke-current"
              fill="none"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6 6 0 0 0-5-5.917V4a1 1 0 1 0-2 0v1.083A6 6 0 0 0 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 0 1-6 0"
              />
            </svg>
            <span class="hidden sm:inline ml-2">Alerts</span>
          </a>
        </div>
      </div>
    </nav>

    <div class="flex-1 overflow-hidden">
      <h1
        class="text-2xl font-bold px-4 sm:px-6 py-4 bg-white z-20 fixed top-14 w-full border-b border-gray-200"
      >
        Menu  {{ $table->name }}
      </h1>
      <div
      class="tabs flex gap-x-2 overflow-x-auto overflow-y-hidden py-2 px-4 sm:px-6 whitespace-nowrap max-w-full no-scrollbar user-select-none cursor-grab fixed top-[132px] w-full bg-white z-10">
        @foreach($categories as $category)
        <div class="tab flex-none px-4 py-2 rounded-full bg-gray-200 cursor-pointer whitespace-nowrap scroll-snap-align-start user-select-none transition-colors duration-200" onclick="filterMenu('{{ $category->name }}')">
            {{ $category->name }}
        </div>
        @endforeach
      </div>
    </div>
    <div class="scroll-area mt-[200px] h-[calc(100vh-250px)] overflow-y-auto pb-20 no-scrollbar px-4 sm:px-6" id="scroll-container">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4" id="menu">
            @foreach($products as $index => $product)
                @php
                    $productImage = asset($product->image ?? 'assets/images/logo/logo.png');
                    $productName = addslashes($product->name);
                    $productImageEscaped = addslashes($productImage);
                @endphp
                <div class="bg-white p-3 rounded-xl shadow-sm hover:shadow-md transition duration-300 flex flex-col items-center space-y-2 h-fit">
                    <img
                        src="{{ $productImage }}"
                        alt="{{ $product->name }}"
                        class="rounded-md w-full h-28 object-cover hover:scale-105 transition-transform duration-300"
                    />
                    <div class="text-center space-y-1">
                        <h4 class="font-bold text-sm text-gray-800">{{ $product->name }}</h4>
                        <p class="text-green-600 font-semibold text-xs">{{ number_format($product->price) }} MMK</p>
                        <p class="text-xs text-gray-500 leading-snug">{{ $product->description }}</p>
                    </div>
                    <button
                        onclick="addToCart({{ $product->id }}, '{{ $productName }}', {{ $product->price }}, '{{ $productImageEscaped }}')"
                        class="mt-auto bg-green-600 text-white px-3 py-1.5 rounded-full text-xs font-semibold hover:bg-green-700 transition w-full"
                    >
                        Add to Cart
                    </button>
                </div>
            @endforeach
        </div>
    </div>


    <div class="footer fixed bottom-0 left-0 w-full flex justify-between items-center px-4 sm:px-6 py-4 bg-green-700 text-white text-base hidden" id="footer-bar" onclick="goToCart()">
        <div class="flex items-center">
          <span class="bg-white text-green-700 rounded-full px-2 py-1 mr-2 font-bold" id="item-count">0</span>
          View your cart
        </div>
        <div id="cart-total">0 MMK</div>
    </div>

    <script>
        let cartCount = 0;
        let cartTotal = 0;
        let currentPage = 1;
        let isLoading = false;
        let selectedCategory = "{{ $categories->first()->name ?? '' }}";

        function addToCart(price, index) {
          const qty = parseInt(document.getElementById(`qty-${index}`).textContent);
          cartCount += qty;
          cartTotal += price * qty;

          document.getElementById("item-count").textContent = cartCount;
          document.getElementById("cart-total").textContent = `${cartTotal.toLocaleString()} MMK`;

          document.getElementById(`counter-${index}`).classList.remove("hidden");
          document.getElementById(`counter-${index}`).classList.add("flex");
          document.getElementById(`btn-${index}`).classList.add("hidden");

          document.getElementById("footer-bar").classList.remove("hidden");
          document.getElementById("footer-bar").classList.add("flex");

          const productId = document.querySelector(`#btn-${index}`).getAttribute("data-product-id");

          const userId = "{{ session('guest_user_id') }}";
          fetch("{{ route('customer.take_away.cart.add') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ product_id: productId, qty: qty, user_id: userId })
          });
        }



function updateQuantity(index, change, price) {
  const qtyEl = document.getElementById(`qty-${index}`);
  let qty = parseInt(qtyEl.textContent);
  const productId = document.querySelector(`#btn-${index}`).getAttribute("data-product-id");

  qty += change;

  if (qty <= 0) {
    document.getElementById(`counter-${index}`).classList.add("hidden");
    document.getElementById(`counter-${index}`).classList.remove("flex");
    document.getElementById(`btn-${index}`).classList.remove("hidden");
    document.getElementById(`btn-${index}`).classList.add("block");
    cartCount--;
    cartTotal -= price;
  } else {
    cartCount += change;
    cartTotal += change * price;
    qtyEl.textContent = qty;
  }

  document.getElementById("item-count").textContent = cartCount;
  document.getElementById("cart-total").textContent = `${cartTotal.toLocaleString()} MMK`;

  // ✅ Correct: Send productId and change (NOT full qty)
        fetch("{{ route('cart.update.ajax') }}", {
            method: "POST",
            headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ product_id: productId, change: change })
        }).then(res => res.json())
            .then(data => console.log("Server updated:", data))
            .catch(err => console.error("Failed to update cart:", err));

        if (cartCount === 0) {
            document.getElementById("footer-bar").classList.add("hidden");
            document.getElementById("footer-bar").classList.remove("flex");
        }
        }



        function goToCart() {
          window.location.href = "{{ route('customer.die-in.cart') }}";
        }

        function filterMenu(categoryName) {
          selectedCategory = categoryName;
          currentPage = 1;
          isLoading = false;
          document.getElementById("menu").innerHTML = "";
          loadMore();

          document.querySelectorAll('.tab').forEach(tab => {
            tab.classList.remove("bg-green-200", "text-green-700", "font-bold");
            tab.classList.add("bg-gray-200");
            if (tab.innerText.trim() === categoryName.trim()) {
              tab.classList.add("bg-green-200", "text-green-700", "font-bold");
              tab.classList.remove("bg-gray-200");
            }
          });
        }
        window.addEventListener('scroll', function () {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 100) {
                loadMore();
            }
        });

        function loadMore() {
          if (isLoading) return;
          isLoading = true;

          fetch(`{{ url('/ajax/products') }}?category=${encodeURIComponent(selectedCategory)}&page=${currentPage}`)
            .then(res => res.text())
            .then(html => {
              document.getElementById("menu").insertAdjacentHTML("beforeend", html);
              isLoading = false;
              currentPage++;
            });
        }

        document.getElementById('scroll-container').addEventListener('scroll', function () {
          const container = this;
          if (container.scrollTop + container.clientHeight >= container.scrollHeight - 10) {
            loadMore();
          }
        });

        window.addEventListener('DOMContentLoaded', () => filterMenu(selectedCategory));
    </script>

  </body>
</html> --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Menu — {{ $table->name }}</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    body { font-family: "Inter", sans-serif; }
    .clear-btn { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); cursor: pointer; color: #aaa; }
    .clear-btn:hover { color: #555; }
  </style>
</head>
<body class="bg-gray-50 h-screen overflow-hidden">

@php
  $sessionCart     = session('cart', []);
  $initialDistinct = count($sessionCart);
  $sessionIds      = array_keys($sessionCart);
  $lastPage        = $products->lastPage();
@endphp

<!-- NAV + SEARCH + TABS -->
<header class="fixed top-0 inset-x-0 z-50 bg-white shadow-md">
  <div class="flex items-center justify-between h-16 px-4 sm:px-6">
    <div class="flex items-center space-x-3">
      <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo" class="h-10 w-10 rounded-full"/>
      <div class="flex flex-col">
        <span class="text-2xl font-extrabold text-green-700">Hello!</span>
        <span class="text-xs text-gray-500">Table: <span class="font-semibold">{{ $table->name }}</span></span>

         @if ($latestOrderToday)
        <span class="text-[11px] text-gray-400 mt-1">
          Last order today:
          <span class="font-semibold">
            {{ strtoupper($latestOrderToday->status) }}
          </span>
          • {{ $latestOrderToday->created_at->format('H:i') }}
        </span>
      @else
        <span class="text-[11px] text-gray-400 mt-1">No orders yet today</span>
      @endif
      </div>

    </div>

    <div class="flex items-center space-x-6 ">
      <a href="{{ route('customer.die_in.home') }}" class="flex items-center text-green-700 hover:text-green-600 transition-colors duration-200">
        <i class="fa-solid fa-house text-xl"></i><span class="hidden sm:inline ml-2">Home</span>
      </a>
      <a href="{{ route('customer.die_in.order_history') }}" class="flex items-center hover:text-green-600 transition-colors duration-200">
        <i class="fa-solid fa-receipt text-xl"></i><span class="hidden sm:inline ml-2">Orders</span>
      </a>

      {{-- DINE-IN cart (table specific) --}}
      <a href="{{ route('customer.die-in.cart', ['table' => $table->id]) }}"
         class="hover:text-green-600 transition inline-flex items-center gap-2">
        <span class="relative inline-block">
          <i class="fa-solid fa-cart-shopping text-xl"></i>
          <span id="nav-cart-count"
                class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold px-1.5 py-0.5 rounded-full leading-none">
            {{ $initialDistinct }}
          </span>
        </span>
        <span class="hidden sm:inline">Cart</span>
      </a>
    </div>
  </div>

  {{-- <div class="px-4 sm:px-6 pt-2">
    <h2 class="text-xl font-bold text-gray-800">Menu • <span class="text-green-700">{{ $table->name }}</span>
     @if ($showChangeTable)
      <form method="POST" action="{{ route('customer.die_in.forget') }}">
        @csrf
        <button
          class="px-3 py-2 rounded-full text-sm font-semibold border border-gray-300 hover:bg-gray-50 transition"
          title="Change to another table by scanning again">
          Change Table
        </button>
      </form>
    @endif</h2>

  </div> --}}
    <div class="w-full flex justify-center my-4">
        <div class="w-full max-w-lg mx-4 relative">
            <input id="global-search" type="search"
                   placeholder="Search products, codes, categories…"
                   class="w-full border border-gray-300 rounded-full py-2 pl-10 pr-10 focus:outline-none focus:ring-2 focus:ring-green-500 transition"/>
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
            <i id="clear-search" class="fa-solid fa-xmark clear-btn hidden absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 cursor-pointer"></i>
        </div>
    </div>
  <div class="px-4 sm:px-6 pt-2">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
    <h2 class="text-xl font-bold text-gray-800">
      Menu • <span class="text-green-700">{{ $table->name }}</span>
    </h2>

    @if ($showChangeTable)
      <form method="POST" action="{{ route('customer.die_in.forget') }}" class="sm:ml-4">
        @csrf
        <button type="submit"
          class="px-3 py-2 rounded-full text-sm font-semibold border border-gray-300 hover:bg-gray-50 transition">
          Change Table
        </button>
      </form>
    @endif
  </div>
</div>


  <div class="bg-white border-t border-gray-200">
    <div class="overflow-x-auto no-scrollbar">
      <div class="flex space-x-2 px-4 sm:px-6 py-3">
        <button class="tab-btn whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-700 font-bold"
                data-category="">All</button>
        @foreach($categories as $category)
          <button class="tab-btn whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium bg-gray-200 text-gray-700 hover:bg-green-100 hover:text-green-700 transition"
                  data-category="{{ $category->name }}">{{ $category->name }}</button>
        @endforeach
      </div>
    </div>
  </div>
</header>

<!-- PRODUCTS GRID -->
<main id="scroll-container" class="pt-48 pb-24 h-full overflow-y-auto no-scrollbar px-4 sm:px-6">
  <div id="menu" class="grid
            grid-cols-2
            max-[360px]:grid-cols-1
            md:grid-cols-3
            lg:grid-cols-4
            xl:grid-cols-5
            gap-6"></div>

  <div id="spinner" class="hidden text-center py-4">
    <i class="fa-solid fa-spinner fa-spin text-2xl text-green-600"></i>
  </div>
  <div id="no-results" class="hidden text-center py-8 text-gray-500">No products found.</div>
</main>

<!-- FOOTER CART BAR -->
<div id="footer-bar" class="fixed bottom-0 inset-x-0 bg-green-700 text-white py-4 px-6 flex items-center justify-between space-x-4 hidden" onclick="goToCart()">
  <span id="item-count" class="bg-white text-green-700 rounded-full px-3 py-1 font-semibold">0</span>
  <button class="ml-auto bg-white text-green-700 px-5 py-2 rounded-full font-semibold hover:bg-gray-100 transition">
    View Cart – <span id="btn-item-count">0</span> items @ <span id="cart-total">0 MMK</span>
  </button>
</div>

<script>
  // table context
  const TABLE_ID = {{ $table->id }};

  // 1) Seed session IDs & counts
  const countedIds   = new Set(@json($sessionIds).map(String));
  const initialIds   = new Set(@json($sessionIds).map(String));
  const initialDistinct = countedIds.size;
  const lastPage     = {{ $lastPage }};

  // 2) State
  let pageDistinct = 0, pageSum = 0, pageTotalMMK = 0,
      currentPage = 1, isLoading = false, selectedCategory = "", searchQuery = "";

  // 3) DOM
  const menuEl      = document.getElementById('menu'),
        spinner     = document.getElementById('spinner'),
        noResults   = document.getElementById('no-results'),
        navBadge    = document.getElementById('nav-cart-count'),
        footerBar   = document.getElementById('footer-bar'),
        pillCount   = document.getElementById('item-count'),
        btnItemCount= document.getElementById('btn-item-count'),
        totalPriceEl= document.getElementById('cart-total'),
        scrollCt    = document.getElementById('scroll-container'),
        tabs        = document.querySelectorAll('.tab-btn'),
        searchInput = document.getElementById('global-search'),
        clearBtn    = document.getElementById('clear-search');

  // 4) Helpers
  function refreshHeader(){ navBadge.textContent = initialDistinct + pageDistinct; }
  function refreshFooter(){
    pillCount.textContent    = pageSum;
    btnItemCount.textContent = pageSum;
    totalPriceEl.textContent = `${pageTotalMMK.toLocaleString()} MMK`;
    footerBar.classList.toggle('hidden', pageSum === 0);
  }

  // 5) Fetch + render (latest first)
  async function loadMore(){
    if(isLoading || currentPage > lastPage) return;
    isLoading = true;
    spinner.classList.remove('hidden');

    const params = new URLSearchParams({ page: currentPage, table_id: TABLE_ID });
    if(searchQuery) params.set('search', searchQuery); else if(selectedCategory) params.set('category', selectedCategory);

    const html = await fetch(`{{ url('/ajax/products') }}?${params}`, { cache: 'no-store' }).then(r=>r.text());
    spinner.classList.add('hidden'); isLoading = false;

    if(!html.trim() && currentPage === 1){
      noResults.classList.remove('hidden');
    } else {
      noResults.classList.add('hidden');
      menuEl.insertAdjacentHTML('beforeend', html);
      currentPage++;
    }
  }

  // 6) Add to cart (table-aware)
  function addToCart(price, index){
    const btn     = document.getElementById(`btn-${index}`),
          pid     = btn.dataset.productId,
          counter = document.getElementById(`counter-${index}`),
          qtyEl   = document.getElementById(`qty-${index}`);

    if(counter.classList.contains('hidden')){
      counter.classList.replace('hidden','flex');
      btn.classList.add('hidden');
      qtyEl.textContent = 1;
      pageSum++;
      if(!countedIds.has(pid)){ countedIds.add(pid); pageDistinct++; }
    } else {
      qtyEl.textContent = parseInt(qtyEl.textContent,10) + 1;
      pageSum++;
    }

    pageTotalMMK += price; refreshFooter(); refreshHeader();

    fetch("{{ route('customer.take_away.cart.add') }}", {
      method:'POST',
      headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}' },
      body: JSON.stringify({ table_id: TABLE_ID, product_id: pid, qty: 1 })
    });
  }

  // 7) Update qty (table-aware)
  function updateQuantity(index, change, price){
    const btn     = document.getElementById(`btn-${index}`),
          pidStr  = String(btn.dataset.productId),
          counter = document.getElementById(`counter-${index}`),
          qtyEl   = document.getElementById(`qty-${index}`);

    let qty = parseInt(qtyEl.textContent,10) + change;

    fetch("{{ route('cart.update.ajax') }}", {
      method:'POST',
      headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}' },
      body: JSON.stringify({ table_id: TABLE_ID, product_id: pidStr, change })
    });

    if(qty <= 0){
      counter.classList.replace('flex','hidden');
      btn.classList.replace('hidden','block');
      pageSum--;
      if (!initialIds.has(pidStr) && countedIds.has(pidStr)) {
        countedIds.delete(pidStr);
        pageDistinct = Math.max(0, pageDistinct - 1);
        refreshHeader();
      }
    } else {
      qtyEl.textContent = qty;
      pageSum += change;
    }

    pageTotalMMK += change * price; refreshFooter();
  }

  // 8) Tabs & Search
  function highlightTab(cat){
    tabs.forEach(t=>{
      const on = t.dataset.category === cat;
      t.classList.toggle('bg-green-100', on);
      t.classList.toggle('text-green-700', on);
      t.classList.toggle('font-bold', on);
      t.classList.toggle('bg-gray-200', !on);
      t.classList.toggle('text-gray-700', !on);
    });
  }

  document.addEventListener('DOMContentLoaded', ()=>{
    refreshHeader(); refreshFooter();

    if(tabs.length){ selectedCategory = ''; highlightTab(selectedCategory); }
    tabs.forEach(t=>t.addEventListener('click',()=>{
      selectedCategory = t.dataset.category; searchQuery = '';
      clearBtn.classList.add('hidden'); searchInput.value='';
      currentPage = 1; menuEl.innerHTML = ''; highlightTab(selectedCategory); loadMore();
    }));

    let dt;
    searchInput.addEventListener('input', e=>{
      clearTimeout(dt); clearBtn.classList.toggle('hidden', !e.target.value);
      dt = setTimeout(()=>{
        searchQuery = e.target.value.trim(); selectedCategory = ''; highlightTab('');
        currentPage = 1; menuEl.innerHTML = ''; loadMore();
      }, 300);
    });
    clearBtn.addEventListener('click', ()=>{
      searchInput.value=''; searchQuery=''; clearBtn.classList.add('hidden');
      currentPage=1; menuEl.innerHTML=''; loadMore();
    });

    let last=0;
    scrollCt.addEventListener('scroll',()=>{
      const now = Date.now();
      if(now - last > 200 && scrollCt.scrollTop + scrollCt.clientHeight >= scrollCt.scrollHeight - 10){
        last = now; loadMore();
      }
    });

    loadMore();
  });

  function goToCart(){
    window.location.href = "{{ route('customer.die-in.cart', ['table' => $table->id]) }}";
  }
</script>

</body>
</html>
