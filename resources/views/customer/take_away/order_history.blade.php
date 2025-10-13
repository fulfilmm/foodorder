{{-- <!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('assets/images/logo/logo.png') }}" type="image/png">
    <title>Dining Option</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap"
      rel="stylesheet"
    />
        <script src="https://kit.fontawesome.com/34ce5b6af8.js" crossorigin="anonymous"></script>

    <style>
      /* Ensure html and body take full viewport height and hide global scrollbar */
      html,
      body {
        height: 100%; /* Make html and body take 100% of the viewport height */
        overflow: hidden; /* Hide the main browser scrollbar */
      }

      /* Custom scrollbar hiding for specific elements (like your content div) */
      .no-scrollbar::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Edge (Chromium-based), Opera */
      }
      .no-scrollbar {
        -ms-overflow-style: none; /* IE and Edge (legacy) */
        scrollbar-width: none; /* Firefox */
      }
    </style>
  </head>
  <body class="font-sans bg-white text-gray-800 min-h-screen flex flex-col">
      @php
      $initialDistinct = count(session('cart', [])); // distinct product_ids
      $cart = session('cart', []);
      $total = 0;
    @endphp

    <!-- NAV -->
    <nav class="fixed inset-x-0 top-0 z-50 bg-white border-b border-green-200">
      <div class="flex h-14 items-center justify-between px-4 sm:px-6">
        <div class="flex items-center space-x-3">
          <div class="h-9 w-9 rounded-full bg-white overflow-hidden">
            <img src="{{asset('assets/images/logo/logo.png')}}" alt="Logo" class="w-full h-full object-cover"/>
          </div>
          <span class="text-xl font-extrabold text-green-700">Hello!</span>
        </div>

        <div class="flex items-center space-x-6">
          <a href="{{route('customer.take_away.home')}}" class="flex items-center hover:text-green-600 transition-colors duration-200">
            <svg class="h-6 w-6 fill-current" viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/></svg>
            <span class="hidden sm:inline ml-2">Home</span>
          </a>

          <a href="{{route('customer.take_away.order_history')}}" class="flex items-center text-green-700 hover:text-green-600 transition-colors duration-200 active">
            <svg class="h-6 w-6 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v16H4zm4 4h8m-8 4h8m-8 4h4"/>
            </svg>
            <span class="hidden sm:inline ml-2">Orders</span>
          </a>

          <!-- Cart link with live badge -->
          <a href="{{ route('customer.take_away.cart') }}" class="hover:text-green-600  transition inline-flex items-center gap-2 ">
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
    </nav>

    <div class="flex-1 overflow-hidden">
      <h1
        class="text-3xl font-bold px-4 sm:px-6 py-4 bg-white z-20 fixed top-14 w-full border-b border-gray-200"
      >
        Order History
      </h1>
      @php
        $statusClasses = [
            'preparing' => 'bg-amber-500',
            'pending' => 'bg-yellow-400',
            'confirmed' => 'bg-blue-500',
            'delivered' => 'bg-green-600',
            'eating' => 'bg-purple-500',
            'done' => 'bg-gray-600',
            'canceled' => 'bg-red-600',
        ];
      @endphp

<div class="h-full overflow-y-auto no-scrollbar px-4 sm:px-6 pt-[148px]">
    @forelse ($orders as $group => $groupOrders)
        <div class="font-bold text-lg mb-2.5">{{ $group }}</div>

        @foreach ($groupOrders as $order)
            <div class="bg-white rounded-xl p-4 mb-4 shadow-md"
                onclick="window.location.href='{{ route('customer.take_away.order_detail', ['id' => $order->id]) }}'" data-order-id="{{ $order->id }}">
                <div class="flex justify-between items-center mb-2.5">
                    <span class="font-bold">Order No: {{ $order->order_no }}</span>
                    <span class="px-3 py-1.5 rounded-full text-xs font-bold text-white
                        {{ $statusClasses[$order->status] ?? 'bg-gray-400' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>

                <div class="flex flex-col gap-2 mb-2.5">
                    @foreach ($order->items as $item)
                        <div class="flex items-center gap-2">
                            <img src="{{ asset($item->product->image ?? 'assets/images/logo/logo.png') }}"
                                 alt="{{ $item->name }}"
                                 class="w-9 h-9 rounded-lg object-cover" />
                            <span>{{ $item->qty }} × {{ $item->name }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-between text-sm text-gray-600">
                    <span>{{ \Carbon\Carbon::parse($order->pickup_date)->format('d/m/Y') }} at {{ $order->pickup_time }}</span>
                    <span>{{ number_format($order->total) }} MMK</span>
                </div>
            </div>
        @endforeach

    @empty
        <div class="text-center text-gray-500 mt-10 text-lg font-semibold">
            🛒 No orders found.
        </div>
    @endforelse
</div>
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.3/echo.iife.js"></script>

<script>
    Pusher.logToConsole = true;

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: "{{ config('broadcasting.connections.pusher.key') }}",
        cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
        forceTLS: true
    });

    Echo.channel('orders')
        .listen('.OrderStatusUpdated', (e) => {
            console.log("🔔 Order Updated (History Page):", e);

            const updatedOrderId = e.order.id;
            const updatedStatus = e.order.status;

            // Find the order card
            const card = document.querySelector(`[data-order-id="${updatedOrderId}"]`);
            if (card) {
                const badge = card.querySelector('span.rounded-full');
                if (badge) {
                    badge.textContent = updatedStatus.charAt(0).toUpperCase() + updatedStatus.slice(1);
                    badge.className = badge.className.replace(/bg-\w+-\d+/, getStatusClass(updatedStatus));
                }
            }
        });

    function getStatusClass(status) {
        const map = {
            pending: 'bg-yellow-400',
            confirmed: 'bg-blue-500',
            preparing: 'bg-amber-500',
            delivered: 'bg-green-600',
            eating: 'bg-purple-500',
            done: 'bg-gray-600',
            canceled: 'bg-red-600',
        };
        return map[status] || 'bg-gray-400';
    }
</script>
  </body>
</html> --}}


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('assets/images/logo/logo.png') }}" type="image/png">
    <title>Order History</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/34ce5b6af8.js" crossorigin="anonymous"></script>

    <style>
      html, body { height: 100%; overflow: hidden; }
      .no-scrollbar::-webkit-scrollbar { display: none; }
      .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
  </head>
  <body class="font-sans bg-white text-gray-800 min-h-screen flex flex-col">
    @php
      $initialDistinct = count(session('cart', []));
      $statusClasses = [
        'pending'   => 'bg-yellow-400',
        'confirmed' => 'bg-blue-500',
        'preparing' => 'bg-amber-500',
        'delivered' => 'bg-green-600',
        'eating'    => 'bg-purple-500',
        'done'      => 'bg-gray-600',
        'canceled'  => 'bg-red-600',
      ];
      $statusBorders = [
        'pending'   => 'border-l-yellow-400',
        'confirmed' => 'border-l-blue-500',
        'preparing' => 'border-l-amber-500',
        'delivered' => 'border-l-green-600',
        'eating'    => 'border-l-purple-500',
        'done'      => 'border-l-gray-400',
        'canceled'  => 'border-l-red-600',
      ];
    @endphp

    <!-- NAV -->
    <nav class="fixed inset-x-0 top-0 z-50 bg-white border-b border-green-200">
      <div class="flex h-14 items-center justify-between px-4 sm:px-6">
        <div class="flex items-center space-x-3">
          <div class="h-9 w-9 rounded-full bg-white overflow-hidden">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo" class="w-full h-full object-cover"/>
          </div>
          <span class="text-xl font-extrabold text-green-700">Hello!</span>
        </div>

        <div class="flex items-center space-x-6">
          <a href="{{ route('customer.take_away.home') }}" class="flex items-center hover:text-green-600 transition-colors duration-200">
            <svg class="h-6 w-6 fill-current" viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"/></svg>
            <span class="hidden sm:inline ml-2">Home</span>
          </a>

          <a href="{{ route('customer.take_away.order_history') }}" class="flex items-center text-green-700 hover:text-green-600 transition-colors duration-200 active">
            <svg class="h-6 w-6 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v16H4zm4 4h8m-8 4h8m-8 4h4"/>
            </svg>
            <span class="hidden sm:inline ml-2">Orders</span>
          </a>

          <a href="{{ route('customer.take_away.cart') }}" class="hover:text-green-600 transition inline-flex items-center gap-2">
            <span class="relative inline-block">
              <i class="fa-solid fa-cart-shopping text-xl"></i>
              <span id="nav-cart-count" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold px-1.5 py-0.5 rounded-full leading-none">{{ $initialDistinct }}</span>
            </span>
            <span class="hidden sm:inline">Cart</span>
          </a>
        </div>
      </div>
    </nav>

    <!-- Page header -->
    <header class="text-3xl font-bold px-4 sm:px-6 py-4 bg-white z-40 fixed top-14 w-full border-b border-gray-200">Order History</header>

    <!-- Content -->
    <div class="flex-1 overflow-hidden">
      <div class="h-full overflow-y-auto no-scrollbar px-4 sm:px-6 pt-[148px] pb-24" id="ordersRoot">
        @forelse ($orders as $group => $groupOrders)
          <div class="text-sm font-bold uppercase tracking-wide text-gray-500 mb-2">{{ $group }}</div>

          <div class="space-y-3">
            @foreach ($groupOrders as $order)
              @php
                $status = $order->status;
                $statusClass = $statusClasses[$status] ?? 'bg-gray-400';
                $borderClass = $statusBorders[$status] ?? 'border-l-gray-300';
                $items = $order->items;
                $visible = $items->take(2);
                $more = max($items->count() - 2, 0);
              @endphp

              <div class="relative bg-white rounded-2xl p-4 shadow-sm border border-gray-100 {{ $borderClass }} border-l-4 order-card" data-order-id="{{ $order->id }}" data-status="{{ $status }}">
                <!-- Top row -->
                <div class="flex items-start justify-between gap-3 mb-3">
                  <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-green-50 text-green-700"><i class="fa-solid fa-receipt text-sm"></i></span>
                    <div class="text-base font-bold">#{{ $order->order_no }}</div>
                    @if (!empty($order->has_comment))
                      <span class="ml-1 inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-800 px-2 py-0.5 text-[11px] font-semibold" title="Order contains item comments">
                        <i class="fa-regular fa-comment-dots"></i> Comments
                      </span>
                    @endif
                  </div>

                  <span class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold text-white status-badge {{ $statusClass }}" data-role="status-badge">
                    {{ ucfirst($status) }}
                  </span>
                </div>

                <!-- Items preview -->
                <div class="flex flex-col gap-2 mb-3">
                  @foreach ($visible as $item)
                    <div class="flex items-center gap-3">
                      <img loading="lazy" src="{{ asset($item->product->image ?? 'assets/images/logo/logo.png') }}" alt="{{ $item->name }}" class="w-10 h-10 rounded-lg object-cover" />
                      <div class="text-sm text-gray-800"><span class="font-semibold">{{ $item->qty }} ×</span> {{ $item->name }}</div>
                    </div>
                  @endforeach
                  @if ($more > 0)
                    <div class="text-xs text-gray-500">+{{ $more }} more item{{ $more > 1 ? 's' : '' }}…</div>
                  @endif
                </div>

                <!-- Meta + Actions -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                  <div class="inline-flex items-center gap-3 text-sm text-gray-600">
                    <span class="inline-flex items-center gap-1"><i class="fa-regular fa-calendar"></i> {{ \Carbon\Carbon::parse($order->pickup_date)->format('d/m/Y') }}</span>
                    <span class="hidden sm:inline">•</span>
                    <span class="inline-flex items-center gap-1"><i class="fa-regular fa-clock"></i> {{ $order->pickup_time }}</span>
                    <span class="hidden sm:inline">•</span>
                    <span class="inline-flex items-center gap-1 font-semibold text-gray-800"><i class="fa-solid fa-coins"></i> {{ number_format($order->total) }} MMK</span>
                   <span class="inline-flex items-center gap-1">
                    <i class="fa-regular fa-calendar"></i>
                    Order Created At :
                    {{ $order->created_at->timezone(config('app.timezone'))->format('d/m/Y | h:i A') }}
                    </span>
                  </div>

                  <div class="flex items-center gap-2">
                    <a href="{{ route('customer.take_away.order_detail', ['id' => $order->id]) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-green-700 text-white text-sm font-bold hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-600">
                      <i class="fa-solid fa-up-right-from-square"></i>
                      Order details
                    </a>
                    <!-- Optional: repeat order action -->
                    {{-- <form action="{{ route('customer.take_away.reorder', ['id' => $order->id]) }}" method="POST">@csrf
                      <button class="inline-flex items-center gap-2 px-3 py-2 rounded-full border border-gray-300 text-sm font-semibold hover:bg-gray-50">
                        <i class="fa-solid fa-rotate-right"></i> Reorder
                      </button>
                    </form> --}}
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @empty
          <div class="text-center text-gray-500 mt-16">
            <div class="text-5xl mb-4">🛒</div>
            <div class="text-lg font-semibold mb-2">No orders yet</div>
            <div class="text-sm mb-4">When you place an order, it will show up here.</div>
            <a href="{{ route('customer.take_away.home') }}" class="inline-flex items-center gap-2 bg-green-700 text-white px-4 py-2 rounded-full font-bold hover:bg-green-800">
              <i class="fa-solid fa-house"></i> Start ordering
            </a>
          </div>
        @endforelse
      </div>
    </div>

    <!-- Realtime updates -->
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.3/echo.iife.js"></script>
    <script>
      Pusher.logToConsole = false; // set true only for debugging
      window.Echo = new Echo({
        broadcaster: 'pusher',
        key: "{{ config('broadcasting.connections.pusher.key') }}",
        cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
        forceTLS: true
      });

      const STATUS_CLASS_MAP = {
        pending:   'bg-yellow-400',
        confirmed: 'bg-blue-500',
        preparing: 'bg-amber-500',
        delivered: 'bg-green-600',
        eating:    'bg-purple-500',
        done:      'bg-gray-600',
        canceled:  'bg-red-600',
      };
      const BORDER_CLASS_MAP = {
        pending:   'border-l-yellow-400',
        confirmed: 'border-l-blue-500',
        preparing: 'border-l-amber-500',
        delivered: 'border-l-green-600',
        eating:    'border-l-purple-500',
        done:      'border-l-gray-400',
        canceled:  'border-l-red-600',
      };

      function swapStatusClasses(el, newClass, dict) {
        const all = Object.values(dict);
        el.classList.remove(...all);
        el.classList.add(newClass);
      }

      Echo.channel('orders').listen('.OrderStatusUpdated', (e) => {
        const { id, status } = e.order || {};
        if (!id || !status) return;

        const card = document.querySelector(`[data-order-id="${id}"]`);
        if (!card) return;

        const badge = card.querySelector('[data-role="status-badge"]');
        if (badge) {
          badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
          swapStatusClasses(badge, STATUS_CLASS_MAP[status] || 'bg-gray-400', STATUS_CLASS_MAP);
        }

        // Update left border colour and data-status attr
        swapStatusClasses(card, BORDER_CLASS_MAP[status] || 'border-l-gray-300', BORDER_CLASS_MAP);
        card.dataset.status = status;
      });
    </script>
  </body>
</html>
