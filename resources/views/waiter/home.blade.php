{{-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Restaurant Orders</title>
  <script src="https://kit.fontawesome.com/34ce5b6af8.js" crossorigin="anonymous"></script>
  <link rel="icon" href="{{asset('assets/images/logo/logo.png')}}" type="image/png" />
  <audio id="notiSound" src="{{ asset('sounds/new_order_receive.wav') }}" preload="auto" allow="autoplay"></audio>

  <style>
    html, body { height: 100%; overflow: hidden; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .tabs.active { cursor: grabbing; }
    body { font-family: "Inter", sans-serif; }
  </style>
</head>
<body class="pt-16 bg-gray-100 text-gray-800">
<nav class="fixed top-0 left-0 right-0 h-14 bg-white shadow flex items-center justify-between px-4 border-b border-green-200 z-50">
  <div class="flex items-center gap-3">
    <div class="w-9 h-9 bg-gray-100 rounded-full overflow-hidden">
      <img src="{{asset('assets/images/logo/logo.png')}}" class="h-full w-full object-cover" />
    </div>
    <span class="text-xl font-extrabold text-green-700">Hello! {{ Auth::user()->name }}</span>
  </div>
  <div class="flex gap-6">
    <a href="{{ route('waiter.home') }}" class="text-green-700 hover:text-green-600 flex items-center gap-1">
      <svg class="h-6 w-6 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
        <path d="M3 10.5L12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10.5Z"/>
      </svg>
      <span class="hidden sm:inline">Home</span>
    </a>
    <form method="POST" action="{{ route('waiter.logout') }}">
      @csrf
      <button type="submit" class="hover:text-red-600 flex items-center gap-1 text-gray-800">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M18 15l3-3m0 0l-3-3m3 3H9"/>
        </svg>
        <span class="hidden sm:inline">Logout</span>
      </button>
    </form>
  </div>
</nav>

<div class="flex h-[calc(100vh-3.5rem)]">
  <!-- Tables -->
  <aside class="w-18 md:w-32 lg:w-48 xl:w-64 bg-white p-4 rounded-xl overflow-y-auto">
    <h2 class="font-bold text-lg text-green-700 mb-4">Tables</h2>
    <ul class="space-y-2">
      @foreach($table as $t)
        @php
          $latestOrder = optional($orders->get($t->id))->first();
          $status      = $latestOrder->status ?? null;
          $isDelivered = $status === 'delivered';
          $isCancelled = $status === 'cancel';
          $isDone      = $status === 'done';
        @endphp

        <li
          onclick="toggleTableActive(this,'Table {{ $t->id }}','{{ $t->id }}','{{ $t->name }}')"
          data-name="{{ $t->name }}"
          class="table-item flex justify-between items-center p-2 rounded hover:bg-green-50 text-green-700 font-semibold cursor-pointer"
        >
          {{ $t->name }}
          <span>
            @if($isDelivered)
              <i class="fa-solid fa-check-circle fa-lg text-green-500"></i>
            @elseif(!$isCancelled && $status && !$isDone)
              <i class="fa-solid fa-clock fa-lg text-gray-400"></i>
            @endif
          </span>
        </li>
      @endforeach
    </ul>
  </aside>

  <!-- Main -->
  <main id="mainContent" class="flex-1 p-6 overflow-hidden space-y-6 relative no-scrollbar">
    <h1 class="text-2xl font-bold text-green-700 mb-6">Orders for <span id="activeTableHeading"></span></h1>

    @foreach($table as $t)
      @php
        $rawOrders  = $orders->get($t->id);
        $mainOrder  = null;
        $addOnOrders= [];

        if ($rawOrders instanceof \Illuminate\Support\Collection) {
          foreach ($rawOrders as $order) {
            $normalizedStatus = strtolower(trim($order->status));
            if (!in_array($normalizedStatus, ['done','cancel'])) {
              if (!$order->parent_order_id) $mainOrder = $order;
              else $addOnOrders[] = $order;
            }
          }
        }
      @endphp

      <section id="order-table-{{ $t->id }}" class="order-section hidden" data-parent-order-id="{{ $mainOrder?->id }}">
        @if($mainOrder)
          <!-- Main + Add-ons -->
          <div class="bg-white rounded-xl p-4 shadow space-y-4 overflow-y-auto max-h-[30vh] no-scrollbar">
            <div class="bg-white rounded-lg p-4 shadow-inner space-y-4">
              <div class="flex justify-between items-center">
                <h3 class="font-semibold text-lg">🎟️ Order No: <span class="font-bold">{{ $mainOrder->order_no }}</span></h3>
                <div class="text-sm px-3 py-1 rounded-full font-semibold bg-green-600 text-white">{{ ucfirst($mainOrder->status) }}</div>
              </div>

              <div class="space-y-3">
                @foreach($mainOrder->items as $item)
                  <div class="flex justify-between items-start">
                    <div class="flex-1">
                      <div class="font-medium text-sm text-gray-800">{{ $item->product->name }}</div>
                      @if($item->comment)
                        <div class="mt-1 text-xs text-amber-800 bg-amber-50 border border-amber-200 inline-flex px-2 py-0.5 rounded">
                          <i class="fa-regular fa-comment-dots mr-1"></i> {{ $item->comment }}
                        </div>
                      @endif
                    </div>
                    <strong class="text-sm text-gray-700">x {{ $item->qty }}</strong>
                  </div>
                @endforeach
              </div>

              <div class="flex justify-between text-sm text-gray-500 border-t pt-2">
                <span>{{ $mainOrder->created_at->format('d/m/Y \a\t h:i A') }}</span>
                <span class="font-bold text-black">{{ number_format($mainOrder->total) }} MMK</span>
              </div>
            </div>

            @foreach($addOnOrders as $addOn)
              <div class="bg-gray-50 rounded-lg p-4 shadow-inner space-y-2">
                <div class="flex justify-between items-center">
                  <h4 class="text-sm font-semibold text-gray-700">➕ Add-On Order: <span class="font-bold">{{ $addOn->order_no }}</span></h4>
                  <span class="text-xs px-3 py-1 rounded-full bg-blue-600 text-white">{{ ucfirst($addOn->status) }}</span>
                </div>
                <div class="space-y-2 text-sm">
                  @foreach($addOn->items as $item)
                    <div class="flex justify-between items-start">
                      <div class="flex-1">
                        <div class="text-gray-800">{{ $item->product->name }}</div>
                        @if($item->comment)
                          <div class="mt-1 text-xs text-amber-800 bg-amber-50 border border-amber-200 inline-flex px-2 py-0.5 rounded">
                            <i class="fa-regular fa-comment-dots mr-1"></i> {{ $item->comment }}
                          </div>
                        @endif
                      </div>
                      <strong>x {{ $item->qty }}</strong>
                    </div>
                  @endforeach
                </div>
                <div class="flex justify-between text-xs text-gray-500 border-t pt-2">
                  <span>{{ $addOn->created_at->format('d/m/Y \a\t h:i A') }}</span>
                  <span class="font-bold text-black">{{ number_format($addOn->total) }} MMK</span>
                </div>
              </div>
            @endforeach
          </div>

          <div class="text-right mt-4" id="add-on-{{ $t->id }}">
            <button onclick="showAddOn('{{ $t->name }}','{{ $t->id }}')" class="bg-green-700 text-white font-bold px-6 py-2 rounded-full hover:bg-green-800">
              Add-On Order
            </button>
          </div>
        @else
          <div id="empty-order-{{ $t->id }}" class="text-center space-y-4 py-12">
            <img src="{{ asset('/assets/images/orders/no-order.jpg') }}" class="w-64 mx-auto" />
            <p>No active orders at this table.</p>
            <button onclick="startOrder('{{ $t->name }}','{{ $t->id }}')" class="bg-green-600 text-white px-6 py-2 rounded-full hover:bg-green-700">
              Start Order
            </button>
          </div>
        @endif
      </section>
    @endforeach

    <!-- Add-on menu -->
    <section id="addOnMenuSection" class="hidden">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-bold text-green-600 text-xl">Menus for <span id="menuTableName"></span></h2>
        <div class="relative w-64">
          <input id="menuSearch" type="text" placeholder="Search name / code / category…" class="w-full rounded-full border border-gray-300 pl-10 pr-9 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500" oninput="queueSearch()" />
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
          <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="clearSearch()" title="Clear">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
      </div>

      <div id="menuTabs" class="overflow-x-auto whitespace-nowrap pb-2 space-x-2 flex no-scrollbar">
        @foreach($categories as $category)
          <button
            class="menu-tab px-4 py-1 rounded-full font-semibold {{ $loop->first ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700' }}"
            onclick="fetchMenu('{{ $category->name }}')"
            data-category="{{ $category->name }}"
          >{{ $category->name }}</button>
        @endforeach
      </div>

      <div class="border rounded-lg border-b-0 shadow-inner p-2 mt-4 overflow-hidden menu-height h-[77vh]">
        <div id="menuItems" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 overflow-y-auto h-full pr-2 pb-[6rem] no-scrollbar"></div>
      </div>
    </section>
  </main>

  <!-- Cart -->
  <aside id="cartPanel" class="hidden w-72 bg-gray-100 p-6 border-l overflow-y-auto">
    <h2 class="text-xl font-bold mb-2">Cart - <span id="cartTableNumber"></span></h2>
    <div id="cartContent"></div>
  </aside>

  <!-- Modals -->
  <div id="successModal" class="fixed inset-0 flex items-center justify-center bg-black/50 hidden z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 text-center max-w-sm w-full">
      <h2 class="text-green-600 font-bold text-xl mb-4">✅ Success</h2>
      <p id="successMessage">Order placed successfully!</p>
      <button onclick="closeModal('successModal')" class="mt-4 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">OK</button>
    </div>
  </div>

  <div id="errorModal" class="fixed inset-0 flex items-center justify-center bg-black/50 hidden z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 text-center max-w-sm w-full">
      <h2 class="text-red-600 font-bold text-xl mb-4">❌ Error</h2>
      <p id="errorMessage">Something went wrong!</p>
      <button onclick="closeModal('errorModal')" class="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Close</button>
    </div>
  </div>
  <button id="unlockAudio" class="hidden"></button>
</div>

<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.3/echo.iife.js"></script>
<script>
  // ===== globals from PHP =====
  const firstCategory   = @json($firstCategoryName);
  const sessionTableId  = @json(session('active_table_id'));
  const sessionTableName= @json(session('active_table_name'));
  const ACTIVE_TAXES    = @json($activeTaxes ?? []);
  const COMBINED_TAX_PERCENT = (ACTIVE_TAXES || []).reduce((a,t)=>a + (+t.percent || 0), 0);

  // ===== local state =====
  let currentTable  = null;
  let activeOrderId = null;
  const carts = {}; // carts[table_id] = { [product_id]: { product_id, name, price, image_path, quantity, comment } }

  let searchTimer = null;
  let currentSearch = '';

  // --- helpers ---
  function fmtMMK(n){ return (Number(n)||0).toLocaleString() + ' MMK'; }
  function escapeHtml(s=''){ return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])); }
  function getActiveCategory(){
    const btn = document.querySelector('.menu-tab.bg-green-600');
    return btn ? btn.dataset.category : firstCategory;
  }

  function queueSearch(){
    clearTimeout(searchTimer);
    currentSearch = document.getElementById('menuSearch').value.trim();
    searchTimer = setTimeout(()=> fetchMenu(getActiveCategory(), currentSearch), 300);
  }
  function clearSearch(){
    const input = document.getElementById('menuSearch');
    input.value = '';
    currentSearch = '';
    fetchMenu(getActiveCategory(), '');
  }

  function toggleTableActive(el, label, id, name) {
    currentTable  = label;
    activeOrderId = id;

    // Persist active table in session (optional)
    fetch('{{ route('store.active.table') }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify({ id, name })
    }).then(r=>r.json()).catch(()=>{});

    document.querySelectorAll('.table-item').forEach(e=>e.classList.remove('bg-green-100'));
    el.classList.add('bg-green-100');

    document.querySelectorAll('.order-section').forEach(e=>e.classList.add('hidden'));
    document.getElementById('order-table-'+id)?.classList.remove('hidden');
    document.getElementById('activeTableHeading').textContent = name;

    if (document.getElementById('addOnMenuSection').classList.contains('shown-'+id)) {
      showAddOn(label,id);
    } else {
      document.getElementById('addOnMenuSection')?.classList.add('hidden');
      document.getElementById('cartPanel')?.classList.add('hidden');
    }
  }

  function startOrder(name, id){
    currentTable = name;
    activeOrderId= id;
    const empty = document.getElementById('empty-order-'+id);
    if (empty) empty.classList.add('hidden');
    showAddOn(name,id,'start');
  }

  function getActiveParentOrderId(tableId){
    const el = document.querySelector(`#order-table-${tableId}`);
    return el?.getAttribute('data-parent-order-id') || null;
  }

  function showAddOn(name, id){
    currentTable = name;
    activeOrderId= id;
    document.getElementById('menuTableName').textContent = name;
    document.getElementById('cartTableNumber').textContent = name;
    document.getElementById('addOnMenuSection')?.classList.remove('hidden');
    document.getElementById('cartPanel')?.classList.remove('hidden');
    document.getElementById('addOnMenuSection').classList.add('shown-'+id);
    document.getElementById('add-on-'+id)?.classList.add('hidden');

    const menuContainer = document.querySelector('#addOnMenuSection .menu-height');
    if (menuContainer) {
      menuContainer.classList.remove('h-[40vh]', 'h-[77vh]');
      const hasMainOrder = !!getActiveParentOrderId(id);
      menuContainer.classList.add(hasMainOrder ? 'h-[40vh]' : 'h-[77vh]');
    }
    setTimeout(()=> document.getElementById('menuSearch')?.focus(), 50);
    fetchMenu(firstCategory, currentSearch);
    renderCart(id);
  }

  // -------- menu fetching with optional search ----------
  function fetchMenu(categoryName, search=''){
    if(!categoryName) return;
    const url = new URL(location.origin + '/ajax/waiter-products');
    url.searchParams.set('category', categoryName);
    if (search) url.searchParams.set('search', search); // backend: accept ?search=
    fetch(url.toString())
      .then(res=>res.text())
      .then(html=>{
        document.getElementById('menuItems').innerHTML = html;
        updateActiveTab(categoryName);
      });
  }

  function updateActiveTab(categoryName){
    document.querySelectorAll('.menu-tab').forEach(b=>{
      const on = b.dataset.category === categoryName;
      b.classList.toggle('bg-green-600', on);
      b.classList.toggle('text-white', on);
      b.classList.toggle('bg-gray-200', !on);
      b.classList.toggle('text-gray-700', !on);
    });
  }

  // -------- CART (safe; keyed by product_id) ----------
  function getActiveCart(){
    if (!carts[activeOrderId]) carts[activeOrderId] = {};
    return carts[activeOrderId];
  }

  function addToCart(product_id, name, price, image){
    const cart = getActiveCart();
    if (cart[product_id]) {
      cart[product_id].quantity += 1;
    } else {
      cart[product_id] = { product_id, name, price, image_path:image, quantity:1, comment:null };
    }
    renderCart(activeOrderId);
  }

  function changeQuantityById(pid, delta){
    const cart = getActiveCart();
    if (!cart[pid]) return;
    cart[pid].quantity += delta;
    if (cart[pid].quantity <= 0) delete cart[pid];
    renderCart(activeOrderId);
  }

  function editCommentById(pid){
    const cart = getActiveCart();
    if (!cart[pid]) return;
    const cur = cart[pid].comment || '';
    const note = prompt('Add / update note for this item:', cur);
    if (note === null) return;
    cart[pid].comment = note.trim();
    renderCart(activeOrderId);
  }

  function renderCart(id){
    const wrap = document.getElementById('cartContent');
    wrap.innerHTML = '';
    const cart  = carts[id] || {};
    const items = Object.values(cart);

    if (items.length === 0){
      wrap.innerHTML = `
        <div class="bg-white rounded-xl p-4 text-center shadow">
          <img src="{{ asset('/assets/images/orders/no-order.jpg') }}" class="w-40 mx-auto mb-2 opacity-80" />
          <p class="text-sm text-gray-500">There are currently no items in the cart.</p>
        </div>`;
      return;
    }

    items.forEach(item=>{
      const row = document.createElement('div');
      row.className = "flex items-start gap-3 mb-3 bg-white p-3 rounded-xl shadow-sm";
      row.innerHTML = `
        <img src="${item.image_path}" class="w-12 h-12 object-cover rounded-md mt-0.5" />
        <div class="flex-1 min-w-0">
          <div class="flex justify-between gap-2">
            <div class="font-semibold text-sm truncate" title="${escapeHtml(item.name)}">${escapeHtml(item.name)}</div>
            <div class="text-xs text-gray-500">${fmtMMK(item.price)}</div>
          </div>

          <div class="mt-1 flex items-center gap-2">
            <button class="btn-dec w-6 h-6 rounded-full bg-gray-100 hover:bg-red-100 text-red-600 font-bold">−</button>
            <span class="w-6 text-center font-semibold text-sm">${item.quantity}</span>
            <button class="btn-inc w-6 h-6 rounded-full bg-gray-100 hover:bg-green-100 text-green-700 font-bold">+</button>

            <button class="btn-note ml-3 text-xs px-2 py-1 rounded-full bg-green-50 text-green-700 border border-green-200 hover:bg-green-100">
              ${item.comment && item.comment.length ? 'Edit note' : 'Add note'}
            </button>
          </div>

          ${item.comment && item.comment.length
            ? `<div class="mt-2 text-xs text-gray-600 border rounded-md p-2 bg-gray-50">
                 <span class="font-semibold text-gray-700">Note:</span>
                 <span>${escapeHtml(item.comment)}</span>
               </div>`
            : ''
          }
        </div>
      `;

      row.querySelector('.btn-dec').addEventListener('click', ()=> changeQuantityById(item.product_id, -1));
      row.querySelector('.btn-inc').addEventListener('click', ()=> changeQuantityById(item.product_id,  1));
      row.querySelector('.btn-note').addEventListener('click', ()=> editCommentById(item.product_id));

      wrap.appendChild(row);
    });

    const subtotal   = items.reduce((s,i)=> s + i.price*i.quantity, 0);
    const taxAmount  = Math.round(subtotal * (COMBINED_TAX_PERCENT/100));
    const grandTotal = subtotal + taxAmount;

    const chips = (ACTIVE_TAXES || [])
      .map(t=>`<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-50 text-green-700 border border-green-200 text-[11px]">${t.name} ${t.percent}%</span>`)
      .join(' ');

    const totals = document.createElement('div');
    totals.className = "mt-4 bg-white rounded-xl p-4 shadow";
    totals.innerHTML = `
      <div class="flex justify-between text-sm text-gray-600"><span>Subtotal</span><span class="font-semibold">${fmtMMK(subtotal)}</span></div>
      <div class="flex justify-between text-sm text-gray-600 mt-1"><span>Tax <span class="text-xs">(${COMBINED_TAX_PERCENT}%)</span></span><span class="font-semibold">${fmtMMK(taxAmount)}</span></div>
      <div class="mt-3 border-t pt-2 flex justify-between text-lg font-bold"><span>Total</span><span>${fmtMMK(grandTotal)}</span></div>
      <div class="mt-2 flex flex-wrap gap-1">${chips}</div>
      <button id="btnConfirmOrder" class="mt-4 w-full py-3 rounded-full bg-green-700 hover:bg-green-800 text-white font-bold">
        Confirm Order
      </button>`;
    wrap.appendChild(totals);

    totals.querySelector('#btnConfirmOrder').addEventListener('click', ()=>{
      confirmOrder(subtotal, taxAmount, COMBINED_TAX_PERCENT, grandTotal);
    });
  }

  function confirmOrder(subtotalFromUI = null, taxAmountFromUI = null, combinedPercentFromUI = null, grandTotalFromUI = null){
    const cart = carts[activeOrderId];
    if (!cart || Object.keys(cart).length === 0){
      alert("Cart is empty.");
      return;
    }
    const items = Object.values(cart).map(i=>({
      product_id: i.product_id,
      name: i.name,
      price: i.price,
      qty: i.quantity,
      comment: i.comment || null,
    }));

    const payload = {
      parent_order_id: getActiveParentOrderId(activeOrderId),
      table_id: activeOrderId,
      cart: items,

      // client snapshot; backend should re-calc
      subtotal: subtotalFromUI,
      tax_amount: taxAmountFromUI,
      tax_percent: combinedPercentFromUI,
      total: grandTotalFromUI,
      tax_snapshot: (ACTIVE_TAXES || []).map(t => `${t.name} ${t.percent}%`).join(' + ')
    };

    fetch('/waiter/checkout', {
      method: 'POST',
      headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify(payload),
    })
    .then(async (res)=>{
      const text = await res.text();
      const data = JSON.parse(text);
      if (!res.ok || data.success === false) throw new Error(data.message || "Failed");
      showModal('successModal', data.message || "Order placed successfully!");
      carts[activeOrderId] = {};
      renderCart(activeOrderId);
    })
    .catch(err=>{
      console.error(err);
      showModal('errorModal', err.message || "Something went wrong.");
    });
  }

  function showModal(id, message){
    const modal = document.getElementById(id);
    const p = modal.querySelector('p');
    if (p && message) p.textContent = message;
    modal.classList.remove('hidden');
  }
  function closeModal(id){
    document.getElementById(id).classList.add('hidden');
    location.reload();
  }

  // ===== boot =====
  document.addEventListener('DOMContentLoaded', ()=>{
    if (firstCategory) {
      fetchMenu(firstCategory, '');
      updateActiveTab(firstCategory);
    }

    // auto-select table from session if possible
    if (sessionTableId && sessionTableName) {
      const target = Array.from(document.querySelectorAll('.table-item'))
        .find(el => el.getAttribute('onclick')?.includes(`'${sessionTableId}'`) && el.textContent.includes(sessionTableName));
      if (target) toggleTableActive(target, `Table ${sessionTableId}`, sessionTableId, sessionTableName);
    } else {
      const first = document.querySelector('.table-item');
      if (first) {
        const args = first.getAttribute('onclick')?.match(/toggleTableActive\(.*?'(.*?)',\s*'(.*?)',\s*'(.*?)'\)/);
        if (args && args.length === 4) toggleTableActive(first, args[1], args[2], args[3]);
      }
    }

    // Echo
    Pusher.logToConsole = true;
    window.Echo = new Echo({
      broadcaster: 'pusher',
      key: "{{ config('broadcasting.connections.pusher.key') }}",
      cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
      forceTLS: true,
      namespace: ''
    });

    Echo.channel('orders')
      .listen('OrderCreated', (e)=>{
        if (e?.order?.order_type === 'dine_in') {
          document.getElementById('notiSound')?.play().catch(()=>{});
          showToast('🛎️ New Dine-In Order Created');
        }
      })
      .listen('OrderStatusUpdated', (e)=>{
        if (e?.order?.order_type === 'dine_in') {
          document.getElementById('notiSound')?.play().catch(()=>{});
          showToast('🛎️ Dine-In Order Updated');
        }
      });
  });

  function showToast(message){
    const t = document.createElement('div');
    t.textContent = message;
    t.className = "fixed top-6 right-6 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-[100]";
    document.body.appendChild(t);
    setTimeout(()=>t.remove(), 2000);
    setTimeout(()=>location.reload(), 2500);
  }
</script>
</body>
</html> --}}



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Restaurant Orders</title>
  <script src="https://kit.fontawesome.com/34ce5b6af8.js" crossorigin="anonymous"></script>
  <link rel="icon" href="{{asset('assets/images/logo/logo.png')}}" type="image/png" />
  <audio id="notiSound" src="{{ asset('sounds/new_order_receive.wav') }}" preload="auto" allow="autoplay"></audio>

  <style>
    html, body { height: 100%; overflow: hidden; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .tabs.active { cursor: grabbing; }
    body { font-family: "Inter", sans-serif; }
  </style>
</head>
<body class="pt-16 bg-gray-100 text-gray-800">
<nav class="fixed top-0 left-0 right-0 h-14 bg-white shadow flex items-center justify-between px-4 border-b border-green-200 z-50">
  <div class="flex items-center gap-3">
    <div class="w-9 h-9 bg-gray-100 rounded-full overflow-hidden">
      <img src="{{asset('assets/images/logo/logo.png')}}" class="h-full w-full object-cover" />
    </div>
    <span class="text-xl font-extrabold text-green-700">Hello! {{ Auth::user()->name }}</span>
  </div>
  <div class="flex gap-6">
    <a href="{{ route('waiter.home') }}" class="text-green-700 hover:text-green-600 flex items-center gap-1">
      <svg class="h-6 w-6 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
        <path d="M3 10.5L12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10.5Z"/>
      </svg>
      <span class="hidden sm:inline">Home</span>
    </a>
    <form method="POST" action="{{ route('waiter.logout') }}">
      @csrf
      <button type="submit" class="hover:text-red-600 flex items-center gap-1 text-gray-800">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M18 15l3-3m0 0l-3-3m3 3H9"/>
        </svg>
        <span class="hidden sm:inline">Logout</span>
      </button>
    </form>
  </div>
</nav>

<div class="flex h-[calc(100vh-3.5rem)]">
  <!-- Tables -->
  <aside class="w-18 md:w-32 lg:w-48 xl:w-64 bg-white p-4 rounded-xl overflow-y-auto">
    <h2 class="font-bold text-lg text-green-700 mb-4">Tables</h2>
    <ul class="space-y-2">
      @foreach($table as $t)
        @php
          // All TODAY's orders for this table (controller already filtered to today)
          $rawOrders = $orders->get($t->id) ?? collect();

          $norm = function($o) { return strtolower(trim($o->status)); };

          // Any order still active (not done/cancel) => show clock
          $hasActive = $rawOrders->contains(function($o) use ($norm) {
            return !in_array($norm($o), ['done','canceled']);
          });

          // No active orders, but at least one done OR delivered => show check
          $hasDoneOrDelivered = $rawOrders->contains(function($o) use ($norm) {
            return in_array($norm($o), ['done','delivered']);
          });
        @endphp

        <li
          onclick="toggleTableActive(this,'Table {{ $t->id }}','{{ $t->id }}','{{ $t->name }}')"
          data-name="{{ $t->name }}"
          class="table-item flex justify-between items-center p-2 rounded hover:bg-green-50 text-green-700 font-semibold cursor-pointer"
        >
          {{ $t->name }}
          <span>
            @if($hasActive)
              <i class="fa-solid fa-clock fa-lg text-gray-400" title="In progress"></i>
            {{-- @elseif($hasDoneOrDelivered)
              <i class="fa-solid fa-check-circle fa-lg text-green-500" title="Completed"></i> --}}
            @endif
          </span>
        </li>
      @endforeach
    </ul>
  </aside>

  <!-- Main -->
  <main id="mainContent" class="flex-1 p-6 overflow-hidden space-y-6 relative no-scrollbar">
    <h1 class="text-2xl font-bold text-green-700 mb-6">Orders for <span id="activeTableHeading"></span></h1>

    {{-- @foreach($table as $t)
      @php
        $rawOrders   = $orders->get($t->id);
        $mainOrder   = null;
        $addOnOrders = [];

        if ($rawOrders instanceof \Illuminate\Support\Collection) {
          foreach ($rawOrders as $order) {
            $st = strtolower(trim($order->status));
            // Treat only non-terminal orders as "active" to show in the main area
            if (!in_array($st, ['done','cancel'])) {
              if (!$order->parent_order_id) $mainOrder = $order;
              else $addOnOrders[] = $order;
            }
          }
        }
      @endphp

      <section id="order-table-{{ $t->id }}" class="order-section hidden" data-parent-order-id="{{ $mainOrder?->id }}">
        @if($mainOrder)
          <!-- Main + Add-ons -->
          <div class="bg-white rounded-xl p-4 shadow space-y-4 overflow-y-auto max-h-[30vh] no-scrollbar">
            <div class="bg-white rounded-lg p-4 shadow-inner space-y-4">
              <div class="flex justify-between items-center">
                <h3 class="font-semibold text-lg">🎟️ Order No: <span class="font-bold">{{ $mainOrder->order_no }}</span></h3>
                <div class="text-sm px-3 py-1 rounded-full font-semibold bg-green-600 text-white">{{ ucfirst($mainOrder->status) }}</div>
              </div>

              <div class="space-y-3">
                @foreach($mainOrder->items as $item)
                  <div class="flex justify-between items-start">
                    <div class="flex-1">
                      <div class="font-medium text-sm text-gray-800">{{ $item->product->name }}</div>
                      @if($item->comment)
                        <div class="mt-1 text-xs text-amber-800 bg-amber-50 border border-amber-200 inline-flex px-2 py-0.5 rounded">
                          <i class="fa-regular fa-comment-dots mr-1"></i> {{ $item->comment }}
                        </div>
                      @endif
                    </div>
                    <strong class="text-sm text-gray-700">x {{ $item->qty }}</strong>
                  </div>
                @endforeach
              </div>

              <div class="flex justify-between text-sm text-gray-500 border-t pt-2">
                <span>{{ $mainOrder->created_at->format('d/m/Y \a\t h:i A') }}</span>
                <span class="font-bold text-black">{{ number_format($mainOrder->total) }} MMK</span>
              </div>
            </div>

            @foreach($addOnOrders as $addOn)
              <div class="bg-gray-50 rounded-lg p-4 shadow-inner space-y-2">
                <div class="flex justify-between items-center">
                  <h4 class="text-sm font-semibold text-gray-700">➕ Add-On Order: <span class="font-bold">{{ $addOn->order_no }}</span></h4>
                  <span class="text-xs px-3 py-1 rounded-full bg-blue-600 text-white">{{ ucfirst($addOn->status) }}</span>
                </div>
                <div class="space-y-2 text-sm">
                  @foreach($addOn->items as $item)
                    <div class="flex justify-between items-start">
                      <div class="flex-1">
                        <div class="text-gray-800">{{ $item->product->name }}</div>
                        @if($item->comment)
                          <div class="mt-1 text-xs text-amber-800 bg-amber-50 border border-amber-200 inline-flex px-2 py-0.5 rounded">
                            <i class="fa-regular fa-comment-dots mr-1"></i> {{ $item->comment }}
                          </div>
                        @endif
                      </div>
                      <strong>x {{ $item->qty }}</strong>
                    </div>
                  @endforeach
                </div>
                <div class="flex justify-between text-xs text-gray-500 border-t pt-2">
                  <span>{{ $addOn->created_at->format('d/m/Y \a\t h:i A') }}</span>
                  <span class="font-bold text-black">{{ number_format($addOn->total) }} MMK</span>
                </div>
              </div>
            @endforeach
          </div>

          <div class="text-right mt-4" id="add-on-{{ $t->id }}">
            <button onclick="showAddOn('{{ $t->name }}','{{ $t->id }}')" class="bg-green-700 text-white font-bold px-6 py-2 rounded-full hover:bg-green-800">
              Add-On Order
            </button>
          </div>
        @else
          <div id="empty-order-{{ $t->id }}" class="text-center space-y-4 py-12">
            <img src="{{ asset('/assets/images/orders/no-order.jpg') }}" class="w-64 mx-auto" />
            <p>No active orders at this table.</p>
            <button onclick="startOrder('{{ $t->name }}','{{ $t->id }}')" class="bg-green-600 text-white px-6 py-2 rounded-full hover:bg-green-700">
              Start Order
            </button>
          </div>
        @endif
      </section>
    @endforeach --}}
    @foreach($table as $t)
  @php
    $rawOrders = $orders->get($t->id) ?? collect(); // today + sorted desc in controller

    // Latest parent (main) order for this table (regardless of status)
    $parentOrder = $rawOrders->firstWhere('parent_order_id', null);

    // All add-ons that belong to this parent (if parent exists),
    // otherwise any order that looks like an add-on
    $allAddOns = $parentOrder
        ? $rawOrders->filter(fn($o) => $o->parent_order_id == $parentOrder->id)
        : $rawOrders->filter(fn($o) => !is_null($o->parent_order_id));

    // Normalize status helper
    $norm = fn($st) => strtolower(trim($st ?? ''));

    // Active add-ons (not done/cancel)
    $activeAddOns = $allAddOns->filter(fn($o) => !in_array($norm($o->status), ['done','canceled']));

    // Is the parent itself active?
    $parentIsActive = $parentOrder && !in_array($norm($parentOrder->status), ['done','canceled']);

    // Show section if parent is active OR any add-on is active
    $hasAnyActive = $parentIsActive || $activeAddOns->isNotEmpty();

    // For JS: prefer real parent id; else fallback to first active add-on's parent id
    $parentOrderIdForJs = $parentOrder->id ?? optional($activeAddOns->first())->parent_order_id;
  @endphp

  <section id="order-table-{{ $t->id }}"
           class="order-section hidden"
           data-parent-order-id="{{ $parentOrderIdForJs }}">
    @if($hasAnyActive)
      <div class="bg-white rounded-xl p-4 shadow space-y-4 overflow-y-auto max-h-[30vh] no-scrollbar">

        {{-- Parent order card: show even if parent is done/cancel (as long as some add-on is still active) --}}
        @if($parentOrder)
          <div class="bg-white rounded-lg p-4 shadow-inner space-y-4">
            <div class="flex justify-between items-center">
              <h3 class="font-semibold text-lg">
                🎟️ Order No: <span class="font-bold">{{ $parentOrder->order_no }}</span>
              </h3>
              <div class="text-sm px-3 py-1 rounded-full font-semibold
                          {{ in_array($norm($parentOrder->status), ['done','cancel']) ? 'bg-gray-500' : 'bg-green-600' }} text-white">
                {{ ucfirst($parentOrder->status) }}
              </div>
            </div>

            <div class="space-y-3">
              @foreach($parentOrder->items as $item)
                <div class="flex justify-between items-start">
                  <div class="flex-1">
                    <div class="font-medium text-sm text-gray-800">{{ $item->product->name }}</div>
                    @if($item->comment)
                      <div class="mt-1 text-xs text-amber-800 bg-amber-50 border border-amber-200 inline-flex px-2 py-0.5 rounded">
                        <i class="fa-regular fa-comment-dots mr-1"></i> {{ $item->comment }}
                      </div>
                    @endif
                  </div>
                  <strong class="text-sm text-gray-700">x {{ $item->qty }}</strong>
                </div>
              @endforeach
            </div>

            <div class="flex justify-between text-sm text-gray-500 border-t pt-2">
              <span>{{ $parentOrder->created_at->format('d/m/Y \a\t h:i A') }}</span>
              <span class="font-bold text-black">{{ number_format($parentOrder->total) }} MMK</span>
            </div>
          </div>
        @endif

        {{-- Active add-ons (show even if parent is done) --}}
        @foreach($activeAddOns as $addOn)
          <div class="bg-gray-50 rounded-lg p-4 shadow-inner space-y-2">
            <div class="flex justify-between items-center">
              <h4 class="text-sm font-semibold text-gray-700">
                ➕ Add-On Order: <span class="font-bold">{{ $addOn->order_no }}</span>
              </h4>
              <span class="text-xs px-3 py-1 rounded-full bg-blue-600 text-white">{{ ucfirst($addOn->status) }}</span>
            </div>
            <div class="space-y-2 text-sm">
              @foreach($addOn->items as $item)
                <div class="flex justify-between items-start">
                  <div class="flex-1">
                    <div class="text-gray-800">{{ $item->product->name }}</div>
                    @if($item->comment)
                      <div class="mt-1 text-xs text-amber-800 bg-amber-50 border border-amber-200 inline-flex px-2 py-0.5 rounded">
                        <i class="fa-regular fa-comment-dots mr-1"></i> {{ $item->comment }}
                      </div>
                    @endif
                  </div>
                  <strong>x {{ $item->qty }}</strong>
                </div>
              @endforeach
            </div>
            <div class="flex justify-between text-xs text-gray-500 border-t pt-2">
              <span>{{ $addOn->created_at->format('d/m/Y \a\t h:i A') }}</span>
              <span class="font-bold text-black">{{ number_format($addOn->total) }} MMK</span>
            </div>
          </div>
        @endforeach
      </div>

      {{-- Add-on button stays available while there is any active context --}}
      <div class="text-right mt-4" id="add-on-{{ $t->id }}">
        <button onclick="showAddOn('{{ $t->name }}','{{ $t->id }}')"
                class="bg-green-700 text-white font-bold px-6 py-2 rounded-full hover:bg-green-800">
          Add-On Order
        </button>
      </div>
    @else
      {{-- Parent and all its add-ons are done/cancel (or nothing today): hide section --}}
      <div id="empty-order-{{ $t->id }}" class="text-center space-y-4 py-12">
        <img src="{{ asset('/assets/images/orders/no-order.jpg') }}" class="w-64 mx-auto" />
        <p>No active orders at this table.</p>
        <button onclick="startOrder('{{ $t->name }}','{{ $t->id }}')"
                class="bg-green-600 text-white px-6 py-2 rounded-full hover:bg-green-700">
          Start Order
        </button>
      </div>
    @endif
  </section>
@endforeach


    <!-- Add-on menu -->
    <section id="addOnMenuSection" class="hidden">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-bold text-green-600 text-xl">Menus for <span id="menuTableName"></span></h2>
        <div class="relative w-64">
          <input id="menuSearch" type="text" placeholder="Search name / code / category…" class="w-full rounded-full border border-gray-300 pl-10 pr-9 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500" oninput="queueSearch()" />
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
          <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="clearSearch()" title="Clear">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
      </div>

      <div id="menuTabs" class="overflow-x-auto whitespace-nowrap pb-2 space-x-2 flex no-scrollbar">
          <button
              class="menu-tab px-4 py-1 rounded-full font-semibold bg-green-600 text-white"
              onclick="fetchMenu('all')"
              data-category="all"
          >All</button>
        @foreach($categories as $category)
          <button
            class="menu-tab px-4 py-1 rounded-full font-semibold {{ $loop->first ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700' }}"
            onclick="fetchMenu('{{ $category->name }}')"
            data-category="{{ $category->name }}"
          >{{ $category->name }}</button>
        @endforeach
      </div>

      <div class="border rounded-lg border-b-0 shadow-inner p-2 mt-4 overflow-hidden menu-height h-[77vh]">
        <div id="menuItems" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 overflow-y-auto h-full pr-2 pb-[6rem] no-scrollbar"></div>
      </div>
    </section>
  </main>

  <!-- Cart -->
  <aside id="cartPanel" class="hidden w-72 bg-gray-100 p-6 border-l overflow-y-auto">
    <h2 class="text-xl font-bold mb-2">Cart - <span id="cartTableNumber"></span></h2>
    <div id="cartContent"></div>
  </aside>

  <!-- Modals -->
  <div id="successModal" class="fixed inset-0 flex items-center justify-center bg-black/50 hidden z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 text-center max-w-sm w-full">
      <h2 class="text-green-600 font-bold text-xl mb-4">✅ Success</h2>
      <p id="successMessage">Order placed successfully!</p>
      <button onclick="closeModal('successModal')" class="mt-4 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">OK</button>
    </div>
  </div>

  <div id="errorModal" class="fixed inset-0 flex items-center justify-center bg-black/50 hidden z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 text-center max-w-sm w-full">
      <h2 class="text-red-600 font-bold text-xl mb-4">❌ Error</h2>
      <p id="errorMessage">Something went wrong!</p>
      <button onclick="closeModal('errorModal')" class="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Close</button>
    </div>
  </div>
  <button id="unlockAudio" class="hidden"></button>
</div>

<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.3/echo.iife.js"></script>
<script>
  // ===== globals from PHP =====
  const firstCategory   = @json($firstCategoryName);
  const sessionTableId  = @json(session('active_table_id'));
  const sessionTableName= @json(session('active_table_name'));
  const ACTIVE_TAXES    = @json($activeTaxes ?? []);
  const COMBINED_TAX_PERCENT = (ACTIVE_TAXES || []).reduce((a,t)=>a + (+t.percent || 0), 0);

  // ===== local state =====
  let currentTable  = null;
  let activeOrderId = null;
  const carts = {}; // carts[table_id] = { [product_id]: { product_id, name, price, image_path, quantity, comment } }

  let searchTimer = null;
  let currentSearch = '';

  // --- helpers ---
  function fmtMMK(n){ return (Number(n)||0).toLocaleString() + ' MMK'; }
  function escapeHtml(s=''){ return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])); }
  function getActiveCategory(){
    const btn = document.querySelector('.menu-tab.bg-green-600');
    return btn ? btn.dataset.category : firstCategory;
  }

  function queueSearch(){
    clearTimeout(searchTimer);
    currentSearch = document.getElementById('menuSearch').value.trim();
    searchTimer = setTimeout(()=> fetchMenu(getActiveCategory(), currentSearch), 300);
  }
  function clearSearch(){
    const input = document.getElementById('menuSearch');
    input.value = '';
    currentSearch = '';
    fetchMenu(getActiveCategory(), '');
  }

  function toggleTableActive(el, label, id, name) {
    currentTable  = label;
    activeOrderId = id;

    // Persist active table in session (optional)
    fetch('{{ route('store.active.table') }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify({ id, name })
    }).then(r=>r.json()).catch(()=>{});

    document.querySelectorAll('.table-item').forEach(e=>e.classList.remove('bg-green-100'));
    el.classList.add('bg-green-100');

    document.querySelectorAll('.order-section').forEach(e=>e.classList.add('hidden'));
    document.getElementById('order-table-'+id)?.classList.remove('hidden');
    document.getElementById('activeTableHeading').textContent = name;

    if (document.getElementById('addOnMenuSection').classList.contains('shown-'+id)) {
      showAddOn(label,id);
    } else {
      document.getElementById('addOnMenuSection')?.classList.add('hidden');
      document.getElementById('cartPanel')?.classList.add('hidden');
    }
  }

  function startOrder(name, id){
    currentTable = name;
    activeOrderId= id;
    const empty = document.getElementById('empty-order-'+id);
    if (empty) empty.classList.add('hidden');
    showAddOn(name,id,'start');
  }

  function getActiveParentOrderId(tableId){
    const el = document.querySelector(`#order-table-${tableId}`);
    return el?.getAttribute('data-parent-order-id') || null;
  }

  function showAddOn(name, id){
    currentTable = name;
    activeOrderId= id;
    document.getElementById('menuTableName').textContent = name;
    document.getElementById('cartTableNumber').textContent = name;
    document.getElementById('addOnMenuSection')?.classList.remove('hidden');
    document.getElementById('cartPanel')?.classList.remove('hidden');
    document.getElementById('addOnMenuSection').classList.add('shown-'+id);
    document.getElementById('add-on-'+id)?.classList.add('hidden');

    const menuContainer = document.querySelector('#addOnMenuSection .menu-height');
    if (menuContainer) {
      menuContainer.classList.remove('h-[40vh]', 'h-[77vh]');
      const hasMainOrder = !!getActiveParentOrderId(id);
      menuContainer.classList.add(hasMainOrder ? 'h-[40vh]' : 'h-[77vh]');
    }
    setTimeout(()=> document.getElementById('menuSearch')?.focus(), 50);
    fetchMenu(firstCategory, currentSearch);
    renderCart(id);
  }

  // -------- menu fetching with optional search ----------
  function fetchMenu(categoryName, search=''){
    if(!categoryName) return;
    const url = new URL(location.origin + '/ajax/waiter-products');
    url.searchParams.set('category', categoryName);
    if (search) url.searchParams.set('search', search); // backend: accept ?search=
    fetch(url.toString())
      .then(res=>res.text())
      .then(html=>{
        document.getElementById('menuItems').innerHTML = html;
        updateActiveTab(categoryName);
      });
  }

  function updateActiveTab(categoryName){
    document.querySelectorAll('.menu-tab').forEach(b=>{
      const on = b.dataset.category === categoryName;
      b.classList.toggle('bg-green-600', on);
      b.classList.toggle('text-white', on);
      b.classList.toggle('bg-gray-200', !on);
      b.classList.toggle('text-gray-700', !on);
    });
  }

  // -------- CART (safe; keyed by product_id) ----------
  function getActiveCart(){
    if (!carts[activeOrderId]) carts[activeOrderId] = {};
    return carts[activeOrderId];
  }

  function addToCart(product_id, name, price, image){
    const cart = getActiveCart();
    if (cart[product_id]) {
      cart[product_id].quantity += 1;
    } else {
      cart[product_id] = { product_id, name, price, image_path:image, quantity:1, comment:null };
    }
    renderCart(activeOrderId);
  }

  function changeQuantityById(pid, delta){
    const cart = getActiveCart();
    if (!cart[pid]) return;
    cart[pid].quantity += delta;
    if (cart[pid].quantity <= 0) delete cart[pid];
    renderCart(activeOrderId);
  }

  function editCommentById(pid){
    const cart = getActiveCart();
    if (!cart[pid]) return;
    const cur = cart[pid].comment || '';
    const note = prompt('Add / update note for this item:', cur);
    if (note === null) return;
    cart[pid].comment = note.trim();
    renderCart(activeOrderId);
  }

  function renderCart(id){
    const wrap = document.getElementById('cartContent');
    wrap.innerHTML = '';
    const cart  = carts[id] || {};
    const items = Object.values(cart);

    if (items.length === 0){
      wrap.innerHTML = `
        <div class="bg-white rounded-xl p-4 text-center shadow">
          <img src="{{ asset('/assets/images/orders/no-order.jpg') }}" class="w-40 mx-auto mb-2 opacity-80" />
          <p class="text-sm text-gray-500">There are currently no items in the cart.</p>
        </div>`;
      return;
    }

    items.forEach(item=>{
      const row = document.createElement('div');
      row.className = "flex items-start gap-3 mb-3 bg-white p-3 rounded-xl shadow-sm";
      row.innerHTML = `
        <img src="${item.image_path}" class="w-12 h-12 object-cover rounded-md mt-0.5" />
        <div class="flex-1 min-w-0">
          <div class="flex justify-between gap-2">
            <div class="font-semibold text-sm truncate" title="${escapeHtml(item.name)}">${escapeHtml(item.name)}</div>
            <div class="text-xs text-gray-500">${fmtMMK(item.price)}</div>
          </div>

          <div class="mt-1 flex items-center gap-2">
            <button class="btn-dec w-6 h-6 rounded-full bg-gray-100 hover:bg-red-100 text-red-600 font-bold">−</button>
            <span class="w-6 text-center font-semibold text-sm">${item.quantity}</span>
            <button class="btn-inc w-6 h-6 rounded-full bg-gray-100 hover:bg-green-100 text-green-700 font-bold">+</button>

            <button class="btn-note ml-3 text-xs px-2 py-1 rounded-full bg-green-50 text-green-700 border border-green-200 hover:bg-green-100">
              ${item.comment && item.comment.length ? 'Edit note' : 'Add note'}
            </button>
          </div>

          ${item.comment && item.comment.length
            ? `<div class="mt-2 text-xs text-gray-600 border rounded-md p-2 bg-gray-50">
                 <span class="font-semibold text-gray-700">Note:</span>
                 <span>${escapeHtml(item.comment)}</span>
               </div>`
            : ''
          }
        </div>
      `;

      row.querySelector('.btn-dec').addEventListener('click', ()=> changeQuantityById(item.product_id, -1));
      row.querySelector('.btn-inc').addEventListener('click', ()=> changeQuantityById(item.product_id,  1));
      row.querySelector('.btn-note').addEventListener('click', ()=> editCommentById(item.product_id));

      wrap.appendChild(row);
    });

    const subtotal   = items.reduce((s,i)=> s + i.price*i.quantity, 0);
    const taxAmount  = Math.round(subtotal * (COMBINED_TAX_PERCENT/100));
    const grandTotal = subtotal + taxAmount;

    const chips = (ACTIVE_TAXES || [])
      .map(t=>`<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-50 text-green-700 border border-green-200 text-[11px]">${t.name} ${t.percent}%</span>`)
      .join(' ');

    const totals = document.createElement('div');
    totals.className = "mt-4 bg-white rounded-xl p-4 shadow";
    totals.innerHTML = `
      <div class="flex justify-between text-sm text-gray-600"><span>Subtotal</span><span class="font-semibold">${fmtMMK(subtotal)}</span></div>
      <div class="flex justify-between text-sm text-gray-600 mt-1"><span>Tax <span class="text-xs">(${COMBINED_TAX_PERCENT}%)</span></span><span class="font-semibold">${fmtMMK(taxAmount)}</span></div>
      <div class="mt-3 border-t pt-2 flex justify-between text-lg font-bold"><span>Total</span><span>${fmtMMK(grandTotal)}</span></div>
      <div class="mt-2 flex flex-wrap gap-1">${chips}</div>
      <button id="btnConfirmOrder" class="mt-4 w-full py-3 rounded-full bg-green-700 hover:bg-green-800 text-white font-bold">
        Confirm Order
      </button>`;
    wrap.appendChild(totals);

    totals.querySelector('#btnConfirmOrder').addEventListener('click', ()=>{
      confirmOrder(subtotal, taxAmount, COMBINED_TAX_PERCENT, grandTotal);
    });
  }

  function confirmOrder(subtotalFromUI = null, taxAmountFromUI = null, combinedPercentFromUI = null, grandTotalFromUI = null){
    const cart = carts[activeOrderId];
    if (!cart || Object.keys(cart).length === 0){
      alert("Cart is empty.");
      return;
    }
    const items = Object.values(cart).map(i=>({
      product_id: i.product_id,
      name: i.name,
      price: i.price,
      qty: i.quantity,
      comment: i.comment || null,
    }));

    const payload = {
      parent_order_id: getActiveParentOrderId(activeOrderId),
      table_id: activeOrderId,
      cart: items,

      // client snapshot; backend should re-calc
      subtotal: subtotalFromUI,
      tax_amount: taxAmountFromUI,
      tax_percent: combinedPercentFromUI,
      total: grandTotalFromUI,
      tax_snapshot: (ACTIVE_TAXES || []).map(t => `${t.name} ${t.percent}%`).join(' + ')
    };

    fetch('/waiter/checkout', {
      method: 'POST',
      headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
      body: JSON.stringify(payload),
    })
    .then(async (res)=>{
      const text = await res.text();
      const data = JSON.parse(text);
      if (!res.ok || data.success === false) throw new Error(data.message || "Failed");
      showModal('successModal', data.message || "Order placed successfully!");
      carts[activeOrderId] = {};
      renderCart(activeOrderId);
    })
    .catch(err=>{
      console.error(err);
      showModal('errorModal', err.message || "Something went wrong.");
    });
  }

  function showModal(id, message){
    const modal = document.getElementById(id);
    const p = modal.querySelector('p');
    if (p && message) p.textContent = message;
    modal.classList.remove('hidden');
  }
  function closeModal(id){
    document.getElementById(id).classList.add('hidden');
    location.reload();
  }

  // ===== boot =====
  document.addEventListener('DOMContentLoaded', ()=>{
    if (firstCategory) {
      fetchMenu(firstCategory, '');
      updateActiveTab(firstCategory);
    }

    // auto-select table from session if possible
    if (sessionTableId && sessionTableName) {
      const target = Array.from(document.querySelectorAll('.table-item'))
        .find(el => el.getAttribute('onclick')?.includes(`'${sessionTableId}'`) && el.textContent.includes(sessionTableName));
      if (target) toggleTableActive(target, `Table ${sessionTableId}`, sessionTableId, sessionTableName);
    } else {
      const first = document.querySelector('.table-item');
      if (first) {
        const args = first.getAttribute('onclick')?.match(/toggleTableActive\(.*?'(.*?)',\s*'(.*?)',\s*'(.*?)'\)/);
        if (args && args.length === 4) toggleTableActive(first, args[1], args[2], args[3]);
      }
    }

    // Echo
    Pusher.logToConsole = true;
    window.Echo = new Echo({
      broadcaster: 'pusher',
      key: "{{ config('broadcasting.connections.pusher.key') }}",
      cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
      forceTLS: true,
      namespace: ''
    });

    Echo.channel('orders')
      .listen('OrderCreated', (e)=>{
        if (e?.order?.order_type === 'dine_in') {
          document.getElementById('notiSound')?.play().catch(()=>{});
          showToast('🛎️ New Dine-In Order Created');
        }
      })
      .listen('OrderStatusUpdated', (e)=>{
        if (e?.order?.order_type === 'dine_in') {
          document.getElementById('notiSound')?.play().catch(()=>{});
          showToast('🛎️ Dine-In Order Updated');
        }
      });
  });

  function showToast(message){
    const t = document.createElement('div');
    t.textContent = message;
    t.className = "fixed top-6 right-6 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-[100]";
    document.body.appendChild(t);
    setTimeout(()=>t.remove(), 2000);
    setTimeout(()=>location.reload(), 2500);
  }
</script>
</body>
</html>
