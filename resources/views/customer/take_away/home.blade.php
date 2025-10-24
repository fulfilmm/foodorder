{{--
<!DOCTYPE html>
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
      .no-scrollbar::-webkit-scrollbar {
        display: none;
      }
      .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
      }
      .tabs.active {
        cursor: grabbing;
      }
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

            <a href="{{ route('customer.take_away.cart') }}"
         class="relative flex items-center hover:text-green-600 transition-colors duration-200">
        <i class="fa-solid fa-cart-shopping text-2xl"></i>
        <span class="hidden sm:inline ml-2">Cart</span>
        <span id="nav-cart-count"
              class="absolute -top-1 -right-2 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-semibold leading-none text-white bg-red-600 rounded-full"
              style="display: none;">0</span>
      </a>
        </div>
      </div>
    </nav>

    <div class="flex-1 overflow-hidden">
      <h1
        class="text-2xl font-bold px-4 sm:px-6 py-4 bg-white z-20 fixed top-14 w-full border-b border-gray-200"
      >
        Menu
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

    <div class="scroll-area mt-[180px] h-[calc(100vh-250px)] overflow-y-auto pb-20 no-scrollbar px-4 sm:px-6" id="scroll-container">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4" id="menu">
          @foreach($products as $index => $product)
          <div class="card rounded-xl shadow-md overflow-hidden text-center p-2 bg-white relative">
            <img src="{{ asset($product->image ?? 'assets/images/logo/logo.png') }}" class="w-full h-32 object-cover mb-2 rounded" />
            <div class="font-bold text-base my-1">{{ $product->name }}</div>
            <div class="text-gray-600 mb-2">{{ number_format($product->price) }} MMK</div>
            <div class="counter hidden flex items-center justify-between bg-gray-100 rounded-full py-1 px-3 w-28 mx-auto mb-2" id="counter-{{ $index }}">
              <button onclick="updateQuantity({{ $index }}, -1, {{ $product->price }})" class="text-lg text-green-700">−</button>
              <span id="qty-{{ $index }}" class="font-bold text-green-700">1</span>
              <button onclick="updateQuantity({{ $index }}, 1, {{ $product->price }})" class="text-lg text-green-700">+</button>
            </div>
            <button id="btn-{{ $index }}" data-product-id="{{ $product->id }}" onclick="addToCart({{ $product->price }}, {{ $index }})" class="btn bg-green-700 text-white py-2 px-4 rounded-full w-full font-bold mt-2">
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

          const userId = "{{ session('guest_user_id') }}"; // Get from Laravel session

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
          window.location.href = "{{ route('customer.take_away.cart') }}";
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
  <title>Dining Option</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .skeleton { background: #e2e8f0; animation: pulse 1.5s infinite; }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
    body { font-family: "Inter", sans-serif; }
    .clear-btn { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); cursor: pointer; color: #aaa; }
    .clear-btn:hover { color: #555; }
  </style>
  <style>
  /* Hide native clear on Chrome/Safari/Edge */
  input[type="search"]::-webkit-search-cancel-button,
  input[type="search"]::-webkit-search-decoration,
  input[type="search"]::-webkit-search-results-button,
  input[type="search"]::-webkit-search-results-decoration {
    display: none;
    -webkit-appearance: none;
    appearance: none;
  }
  /* Hide native clear on old Edge/IE */
  input[type="search"]::-ms-clear,
  input[type="search"]::-ms-reveal {
    display: none;
    width: 0;
    height: 0;
  }
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
        <span class="text-2xl font-extrabold text-green-700">Hello!</span>
      </div>
      <div class="flex items-center space-x-6 ">
        <a href="{{ route('customer.take_away.home') }}" class="flex items-center text-green-700 hover:text-green-600 transition-colors duration-200 active ">
            <i class="fa-solid fa-house text-xl"></i>
            <span class="hidden sm:inline ml-2">Home</span>
        </a>
        <a href="{{ route('customer.take_away.order_history') }}" class="flex items-center hover:text-green-600 transition-colors duration-200"><i class="fa-solid fa-receipt text-xl"></i>
        <span class="hidden sm:inline ml-2">Orders</span></a>
        <a href="{{ route('customer.take_away.cart') }}" class="hover:text-green-600 transition inline-flex items-center gap-2">
            <!-- Icon wrapper is relative so the badge anchors to the icon, not the whole link -->
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
      <div class="w-full flex justify-center my-4">
          <div class="w-full max-w-lg mx-4 relative">
              <input id="global-search" type="search"
                     placeholder="Search products, codes, categories…"
                     class="w-full border border-gray-300 rounded-full py-2 pl-10 pr-10 focus:outline-none focus:ring-2 focus:ring-green-500 transition"/>
              <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
              <i id="clear-search" class="fa-solid fa-xmark clear-btn hidden absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 cursor-pointer"></i>
          </div>
      </div>

      <div class="px-4 sm:px-6 pt-4"><h2 class="text-xl font-bold text-gray-800">Menu</h2></div>
    <div class="bg-white border-t border-gray-200">
      <div class="overflow-x-auto no-scrollbar">
        <div class="flex space-x-2 px-4 sm:px-6 py-3">
          <button class="tab-btn whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium bg-gray-200 text-gray-700 hover:bg-green-100 hover:text-green-700 transition"
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
              /* default for >360px and <768px is 2 columns: */
              grid-cols-2
              /* up to 360px: force 1 column */
              max-[360px]:grid-cols-1
              /* ≥768px: 3 cols */
              md:grid-cols-3
              /* ≥1024px: 4 cols */
              lg:grid-cols-4
              /* ≥1280px: 5 cols */
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
    // 1) Seed session IDs & counts
    const countedIds      = new Set(@json($sessionIds).map(i=>i.toString()));
    const initialIds  = new Set(@json($sessionIds).map(String)); // ids present at page load

    const initialDistinct = countedIds.size;
    const lastPage        = {{ $lastPage }};

    // 2) State
    let pageDistinct    = 0,
        pageSum         = 0,
        pageTotalMMK    = 0,
        currentPage     = 1,
        isLoading       = false,
        selectedCategory= "",
        searchQuery     = "";

    // 3) Cache DOM
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

    // 4) Refresh helpers
    function refreshHeader(){
      navBadge.textContent = initialDistinct + pageDistinct;
    }
    function refreshFooter(){
      pillCount.textContent    = pageSum;
      btnItemCount.textContent = pageSum;
      totalPriceEl.textContent = `${pageTotalMMK.toLocaleString()} MMK`;
      footerBar.classList.toggle('hidden', pageSum === 0);
    }

    // 5) Fetch + render
    async function loadMore(){
      if(isLoading || currentPage > lastPage) return;
      isLoading = true;
      spinner.classList.remove('hidden');

      const params = new URLSearchParams({ page: currentPage });
      if(searchQuery) params.set('search', searchQuery);
      else if(selectedCategory) params.set('category', selectedCategory);

      const html = await fetch(`{{ url('/ajax/products') }}?${params}`).then(r=>r.text());
      spinner.classList.add('hidden');
      isLoading = false;

      if(!html.trim() && currentPage === 1){
        noResults.classList.remove('hidden');
      } else {
        noResults.classList.add('hidden');
        menuEl.insertAdjacentHTML('beforeend', html);
        currentPage++;
      }
    }

    // 6) Add to cart
    function addToCart(price, index){
      const btn     = document.getElementById(`btn-${index}`),
        productId = document.querySelector(`#btn-${index}`).getAttribute("data-product-id"),
            pid     = btn.dataset.productId,
            counter = document.getElementById(`counter-${index}`),
            qtyEl   = document.getElementById(`qty-${index}`);

      if(counter.classList.contains('hidden')){
        counter.classList.replace('hidden','flex');
        btn.classList.add('hidden');
        qtyEl.textContent = 1;
        pageSum++;
        if(!countedIds.has(pid)){
          countedIds.add(pid);
          pageDistinct++;
        }
      } else {
        qtyEl.textContent = parseInt(qtyEl.textContent,10) + 1;
        pageSum++;
      }

      pageTotalMMK += price;
      refreshFooter();
      refreshHeader();

      fetch("{{ route('customer.take_away.cart.add') }}", {
        method:'POST',
        headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}' },
        body: JSON.stringify({ product_id: pid, qty:1, user_id:"{{ session('guest_user_id') }}" })
      });
    }

    // 7) Update quantity
    function updateQuantity(index, change, price){
      const btn     = document.getElementById(`btn-${index}`),
            counter = document.getElementById(`counter-${index}`),
             pidStr  = String(btn.dataset.productId), // <-- normalize

            pid     = btn.dataset.productId,
            qtyEl   = document.getElementById(`qty-${index}`);

      let qty = parseInt(qtyEl.textContent,10) + change;
      fetch("{{ route('cart.update.ajax') }}", {
        method:'POST',
        headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}' },
        body: JSON.stringify({ product_id: btn.dataset.productId, change })
      });
      if(qty <= 0){
        counter.classList.replace('flex','hidden');
        btn.classList.replace('hidden','block');
        pageSum--;
        console.log(countedIds)
        console.log(pid);
        if (!initialIds.has(pidStr) && countedIds.has(pidStr)) {
      countedIds.delete(pidStr);
      pageDistinct = Math.max(0, pageDistinct - 1);
      refreshHeader();
    }
      } else {
        qtyEl.textContent = qty;
        pageSum += change;
      }

      pageTotalMMK += change * price;
      refreshFooter();
    //   refreshHeader();



    //   fetch("{{ route('cart.update.ajax') }}", {
    //     method:'POST',
    //     headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}' },
    //     body: JSON.stringify({ product_id: btn.dataset.productId, change })
    //   });

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

    document.addEventListener('DOMContentLoaded',()=>{
      refreshHeader();
      refreshFooter();

      // tabs
      if(tabs.length){
        selectedCategory = tabs[0].dataset.category;
        highlightTab(selectedCategory);
      }
      tabs.forEach(t=>t.addEventListener('click',()=>{
        selectedCategory = t.dataset.category;
        searchQuery      = '';
        clearBtn.classList.add('hidden');
        searchInput.value='';
        currentPage      = 1;
        menuEl.innerHTML = '';
        highlightTab(selectedCategory);
        loadMore();
      }));

      // debounced search
      let dt;
      searchInput.addEventListener('input',e=>{
        clearTimeout(dt);
        clearBtn.classList.toggle('hidden', !e.target.value);
        dt = setTimeout(()=>{
          searchQuery      = e.target.value.trim();
          selectedCategory = '';
          tabs.forEach(x=>highlightTab(''));
          currentPage      = 1;
          menuEl.innerHTML = '';
          loadMore();
        },300);
      });

      clearBtn.addEventListener('click',()=>{
        searchInput.value='';
        searchQuery='';
        clearBtn.classList.add('hidden');
        currentPage=1;
        menuEl.innerHTML='';
        loadMore();
      });

      // throttled scroll on container
      let last=0;
      scrollCt.addEventListener('scroll',()=>{
        const now = Date.now();
        if(now - last > 200 && scrollCt.scrollTop + scrollCt.clientHeight >= scrollCt.scrollHeight - 10){
          last = now;
          loadMore();
        }
      });

      // first fetch
      loadMore();
    });

    function goToCart(){
      window.location.href = "{{ route('customer.take_away.cart') }}";
    }
  </script>

</body>
</html>






