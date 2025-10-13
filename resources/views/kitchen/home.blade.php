{{-- resources/views/kitchen/home.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Food Order | Kitchen Panel</title>
  <link rel="icon" href="{{ asset('assets/images/logo/logo.png') }}" type="image/png" />
  <script src="https://kit.fontawesome.com/34ce5b6af8.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
  <audio id="notiSound" src="{{ asset('sounds/new_order_receive.wav') }}" preload="auto" allow="autoplay"></audio>

  <style>
    body { font-family: "Inter", sans-serif; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
  </style>
</head>
<body class="bg-gray-100 pt-28 p-4">

@php
  use Illuminate\Support\Str;

  $statusClasses = [
    'pending'   => 'bg-yellow-400 text-white',
    'confirmed' => 'bg-blue-500 text-white',
    'preparing' => 'bg-amber-500 text-white',
    'delivered' => 'bg-green-600 text-white',
    'canceled'  => 'bg-red-600 text-white',
  ];
@endphp

<!-- Navbar -->
<div class="bg-white fixed top-0 left-0 right-0 z-50">
  <nav class="h-14 bg-white shadow flex items-center justify-between px-4">
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 bg-gray-100 rounded-full overflow-hidden">
        <img src="{{ asset('assets/images/logo/logo.png') }}" class="h-full w-full object-cover" />
      </div>
      <span class="text-xl font-extrabold text-green-700">Hello!</span>
      <span class="text-sm">Welcome <b>{{ Auth::user()->name }}</b> — let’s cook! 👩‍🍳👨‍🍳</span>
    </div>

    <div class="flex gap-6">
      <a href="{{ route('kitchen.home') }}" class="text-green-700 hover:text-green-600 flex items-center gap-1">
        <svg class="h-6 w-6 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
          <path d="M3 10.5L12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10.5Z" />
        </svg>
        <span class="hidden sm:inline">Home</span>
      </a>

      <form method="POST" action="{{ route('kitchen.logout') }}">
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

  <!-- Filter + Search -->
  <div class="top-14 bg-white py-2 border-b border-green-200 shadow-sm px-4">
    <div class="flex items-center gap-2 justify-between overflow-x-auto no-scrollbar">
      <div class="flex gap-2 shrink-0">
        <button onclick="filterOrders('all')" id="filter-all" class="bg-green-100 text-green-800 px-4 py-1.5 rounded-full text-sm font-semibold">All Orders</button>
        <button onclick="filterOrders('new')" id="filter-new" class="bg-gray-200 text-gray-800 px-4 py-1.5 rounded-full text-sm font-semibold">New Orders</button>
        @foreach($statusClasses as $status => $class)
          <button onclick="filterOrders('{{ $status }}')" id="filter-{{ $status }}" class="bg-gray-200 text-gray-800 px-4 py-1.5 rounded-full text-sm font-semibold">
            {{ ucfirst($status) }} Orders
          </button>
        @endforeach
      </div>

      <!-- Search -->
      <div class="relative w-full max-w-xs ml-2">
        <form action="{{ route('kitchen.home') }}" method="GET" class="contents">
          <input
            id="orderSearch"
            name="q"
            value="{{ $q ?? '' }}"
            type="text"
            placeholder="Search by Order No or Item…  (press /)"
            class="w-full rounded-full border border-gray-300 pl-10 pr-9 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
            oninput="debouncedApplyFilters()"
          />
        </form>
        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
        <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="clearSearch()" title="Clear">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Order Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 overflow-y-auto relative no-scrollbar max-h-[85vh] mt-4">
  @forelse($orders as $status => $statusOrders)
    @foreach($statusOrders as $order)
      @php
        $searchBlob = Str::lower($order->order_no . ' ' . $order->items->pluck('name')->join(' '));
      @endphp

      <div class="bg-white rounded-2xl shadow p-4 flex flex-col gap-4 h-fit order-card"
           data-status="{{ $order->status }}"
           data-order-id="{{ $order->id }}"
           data-created-at="{{ $order->created_at->timestamp }}"
           data-search="{{ e($searchBlob) }}">
        <div>
          <div class="flex justify-between items-center mb-2">
            <h2 class="text-lg font-bold text-green-700">
              {{ $order->order_type === 'dine_in' ? optional($order->table)->name : 'Takeaway' }}
            </h2>
            <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $statusClasses[$order->status] ?? 'bg-gray-200 text-gray-700' }}">
              {{ ucfirst($order->status) }}
            </span>
          </div>

          <div class="flex justify-between items-center mb-2">
            <p class="text-sm text-gray-600">
              <i class="fas fa-ticket-alt mr-1"></i><strong>Order No:</strong> {{ $order->order_no }}
            </p>
            <p class="text-sm text-gray-600">
              <i class="fas fa-clock mr-1"></i><strong>Created:</strong> {{ $order->created_at->diffForHumans() }}
            </p>
          </div>

          @if($order->order_type === 'takeaway')
            <p class="text-sm text-gray-600 mb-3"><strong>Pick Up Time:</strong> {{ $order->pickup_time }}</p>
          @endif

          <ul class="divide-y divide-gray-200 mb-4">
            @foreach ($order->items as $item)
              <li class="py-1 flex justify-between font-medium">
                <span>{{ $item->name }}   @if($item->comment)
                          <div class="mt-1 text-xs text-amber-800 bg-amber-50 border border-amber-200 inline-flex px-2 py-0.5 rounded">
                            <i class="fa-regular fa-comment-dots mr-1"></i>Note: {{ $item->comment }}
                          </div>
                @endif</span>

                <span class="text-green-700">{{ $item->qty }}x</span>

              </li>
            @endforeach
          </ul>
        </div>

        @php
          $btnText = '';
          $btnClass = 'py-2 rounded-full font-semibold w-full ';
          $btnDisabled = false;
          $showCancel = false;

          switch ($order->status) {
            case 'pending':
              $btnText = 'Confirm Order';
              $btnClass .= 'bg-yellow-400 hover:bg-yellow-500 text-white';
              $showCancel = true;
              break;
            case 'confirmed':
              $btnText = 'Start Preparing';
              $btnClass .= 'bg-blue-500 hover:bg-blue-600 text-white';
              break;
            case 'preparing':
              $btnText = 'Mark as Delivered';
              $btnClass .= 'bg-amber-500 hover:bg-amber-600 text-white';
              break;
            default:
              $btnText = ucfirst($order->status);
              $btnClass .= $statusClasses[$order->status] ?? 'bg-gray-400 text-white';
              $btnDisabled = true;
          }
        @endphp

        @if ($showCancel)
          <div class="flex gap-2">
            <button onclick="changeStatus({{ $order->id }}, this)"
                    class="{{ $btnClass }} flex-1 {{ $btnDisabled ? 'opacity-50 cursor-not-allowed' : '' }}"
                    {{ $btnDisabled ? 'disabled' : '' }}>
              {{ $btnText }}
            </button>
            <button onclick="cancelOrder({{ $order->id }}, this)"
                    class="flex-1 py-2 rounded-full bg-red-600 hover:bg-red-700 text-white font-semibold">
              Cancel
            </button>
          </div>
        @else
          <button onclick="changeStatus({{ $order->id }}, this)"
                  class="{{ $btnClass }} w-full {{ $btnDisabled ? 'opacity-50 cursor-not-allowed' : '' }}"
                  {{ $btnDisabled ? 'disabled' : '' }}>
            {{ $btnText }}
          </button>
        @endif
      </div>
    @endforeach
  @empty
    <div id="no-orders" class="text-center text-gray-500 text-lg font-semibold w-full py-10">No orders found.</div>
  @endforelse
</div>

<!-- Pusher & Echo -->
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.3/echo.iife.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    Pusher.logToConsole = true;

    window.Echo = new Echo({
      broadcaster: 'pusher',
      key: "{{ config('broadcasting.connections.pusher.key') }}",
      cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
      forceTLS: true,
      namespace: ''
    });

    Echo.channel('orders')
      .listen('OrderStatusUpdated', (e) => {
        // You can avoid reload and patch the card if you want, but reload is simplest
        location.reload();
      })
      .listen('OrderCreated', (e) => {
        const sound = document.getElementById('notiSound');
        sound?.play().catch(()=>{});
        showToast('🛎️ New Order Received');
        addNewOrderCard(e.order);
        applyFilters(); // reflect current filters including search
      });
  });
</script>

<!-- Helpers + Filtering -->
<script>
  let activeStatus = 'all';
  let searchTimer  = null;

  // Press "/" to focus search
  document.addEventListener('keydown', (e)=>{
    const tag = (document.activeElement?.tagName || '').toUpperCase();
    if (e.key === '/' && tag !== 'INPUT' && tag !== 'TEXTAREA') {
      e.preventDefault();
      const s = document.getElementById('orderSearch');
      if (s) { s.focus(); s.select(); }
    }
  });

  function clearSearch() {
    const s = document.getElementById('orderSearch');
    if (s) { s.value = ''; }
    applyFilters();
  }
  function debouncedApplyFilters(){
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 200);
  }

  function filterOrders(status) {
    activeStatus = status || 'all';
    applyFilters();
  }

  function applyFilters() {
    const q = (document.getElementById('orderSearch')?.value || '').trim().toLowerCase();
    const cards = document.querySelectorAll('.order-card');
    const now = Math.floor(Date.now() / 1000);
    const thirtyMinutesAgo = now - 1800;

    let anyVisible = false;

    cards.forEach(card => {
      const cardStatus = card.dataset.status || '';
      const createdAt  = parseInt(card.dataset.createdAt || '0', 10);
      const searchable = (card.dataset.search || '').toLowerCase();

      const statusMatch = (activeStatus === 'all')
        ? true
        : (activeStatus === 'new' ? createdAt >= thirtyMinutesAgo : cardStatus === activeStatus);

      const searchMatch = q === '' ? true : searchable.includes(q);

      const show = statusMatch && searchMatch;
      card.classList.toggle('hidden', !show);
      if (show) anyVisible = true;
    });

    const noOrders = document.getElementById("no-orders");
    if (noOrders) noOrders.classList.toggle("hidden", anyVisible);

    // highlight active filter
    document.querySelectorAll('.top-14 button[id^="filter-"]').forEach(btn => {
      btn.classList.remove('bg-green-100', 'text-green-800');
      btn.classList.add('bg-gray-200', 'text-gray-800');
    });
    const activeBtn = document.getElementById(`filter-${activeStatus}`);
    if (activeBtn) {
      activeBtn.classList.remove('bg-gray-200', 'text-gray-800');
      activeBtn.classList.add('bg-green-100', 'text-green-800');
    }
  }

  // Add a new card on push (keeps it searchable)
  function addNewOrderCard(order) {
    const statusMap = {
      pending: 'bg-yellow-400 text-white',
      confirmed: 'bg-blue-500 text-white',
      preparing: 'bg-amber-500 text-white',
      delivered: 'bg-green-600 text-white',
      canceled: 'bg-red-600 text-white',
    };
    const statusClass = statusMap[order.status] || 'bg-gray-200 text-gray-700';
    const isDineIn = order.order_type === 'dine_in';
    const locationText = isDineIn ? (order.table?.name || 'Table N/A') : 'Takeaway';

    const createdAt = new Date(order.created_at);
    const createdTimestamp = Math.floor(createdAt.getTime() / 1000);

    const itemsHtml = (order.items || []).map(item => `
      <li class="py-1 flex justify-between font-medium">
        <span>${item.name}</span>
        <span class="text-green-700">${item.qty}x</span>
      </li>
    `).join('');

    // primary button
    let btnText = '', btnClass = 'py-2 rounded-full font-semibold w-full ', btnDisabled = false;
    switch (order.status) {
      case 'pending':   btnText = 'Confirm Order';     btnClass += 'bg-yellow-400 hover:bg-yellow-500 text-white'; break;
      case 'confirmed': btnText = 'Start Preparing';   btnClass += 'bg-blue-500 hover:bg-blue-600 text-white';    break;
      case 'preparing': btnText = 'Mark as Delivered'; btnClass += 'bg-amber-500 hover:bg-amber-600 text-white';  break;
      default:          btnText = order.status.charAt(0).toUpperCase() + order.status.slice(1); btnClass += statusClass; btnDisabled = true;
    }

    const card = document.createElement('div');
    card.className = 'bg-white rounded-2xl shadow p-4 flex flex-col gap-4 h-fit order-card';
    card.dataset.status     = order.status;
    card.dataset.createdAt  = createdTimestamp;
    card.dataset.orderId    = order.id;
    card.dataset.search     = (order.order_no + ' ' + (order.items||[]).map(i=>i.name).join(' ')).toLowerCase();

    const pickupHtml = (!isDineIn && order.pickup_time)
      ? `<p class="text-sm text-gray-600 mb-3"><strong>Pick Up Time:</strong> ${order.pickup_time}</p>`
      : '';

    card.innerHTML = `
      <div>
        <div class="flex justify-between items-center mb-2">
          <h2 class="text-lg font-bold text-green-700">${locationText}</h2>
          <span class="px-3 py-1 rounded-full text-sm font-semibold ${statusClass}">
            ${order.status.charAt(0).toUpperCase() + order.status.slice(1)}
          </span>
        </div>
        <div class="flex justify-between items-center mb-2">
          <p class="text-sm text-gray-600">
            <i class="fas fa-ticket-alt mr-1"></i><strong>Order No:</strong> ${order.order_no}
          </p>
          <p class="text-sm text-gray-600">
            <i class="fas fa-clock mr-1"></i><strong>Created:</strong> just now
          </p>
        </div>
        ${pickupHtml}
        <ul class="divide-y divide-gray-200 mb-4">
          ${itemsHtml}
        </ul>
      </div>
      <button onclick="changeStatus(${order.id}, this)"
              class="${btnClass} ${btnDisabled ? 'opacity-50 cursor-not-allowed' : ''}"
              ${btnDisabled ? 'disabled' : ''}>
        ${btnText}
      </button>
    `;

    const grid = document.querySelector('.grid');
    grid?.prepend(card);

    const noOrders = document.getElementById('no-orders');
    if (noOrders) noOrders.classList.add('hidden');
  }

  // Status actions
  function changeStatus(orderId, button) {
    button.disabled = true;
    const prevText = button.innerText;
    button.innerText = 'Updating...';

    fetch(`/orders/${orderId}/status`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({})
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) location.reload();
      else {
        alert(data.message || "Status update failed.");
        button.disabled = false;
        button.innerText = prevText;
      }
    })
    .catch(() => {
      alert("Request failed.");
      button.disabled = false;
      button.innerText = prevText;
    });
  }

  function cancelOrder(orderId, button) {
    if (!confirm('Are you sure you want to cancel this order?')) return;

    button.disabled = true;
    const prevText = button.innerText;
    button.innerText = 'Cancelling...';

    fetch(`/orders/${orderId}/cancel`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({})
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) location.reload();
      else {
        alert(data.message || "Failed to cancel.");
        button.disabled = false;
        button.innerText = prevText;
      }
    })
    .catch(() => {
      alert("Cancel request failed.");
      button.disabled = false;
      button.innerText = prevText;
    });
  }

  function showToast(message) {
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.className = "fixed top-6 right-6 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-[100]";
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2000);
  }

  // Boot: apply filters once on load (respects ?q=)
  document.addEventListener('DOMContentLoaded', applyFilters);
</script>

</body>
</html>
