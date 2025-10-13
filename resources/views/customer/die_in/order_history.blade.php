<!DOCTYPE html>
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
          $initialDistinct = count(session('cart', []));

    @endphp
     <nav class="fixed inset-x-0 top-0 z-50 bg-white border-b border-green-200">
      <div class="flex h-14 items-center justify-between px-4 sm:px-6">
        <div class="flex items-center space-x-3">
          <div class="h-9 w-9 rounded-full overflow-hidden">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo" class="w-full h-full object-cover"/>
          </div>
          <span class="text-xl font-extrabold text-green-700">Hello!</span>
        </div>

        <div class="flex items-center space-x-6">
          <a href="{{ route('customer.die_in.home') }}" class="flex items-center hover:text-green-600 transition">
            <i class="fa-solid fa-house text-xl"></i><span class="hidden sm:inline ml-2">Home</span>
          </a>

          <a href="{{ route('customer.die_in.order_history') }}" class="flex items-center hover:text-green-600 transition">
            <i class="fa-solid fa-receipt text-xl"></i><span class="hidden sm:inline ml-2">Orders</span>
          </a>

          <a href="{{ route('customer.die-in.cart') }}" class="hover:text-green-600 text-green-700 transition inline-flex items-center gap-2 active">
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
        {{-- @php
        $items = $order->items;
         $status = $order->status;
$visible = $items->take(2);
                $more = max($items->count() - 2, 0);
    @endphp
            <div class="bg-white rounded-xl p-4 mb-4 shadow-md"
                onclick="window.location.href='{{ route('customer.die_in.order_detail', ['id' => $order->id]) }}'" data-order-id="{{ $order->id }}">
                <div class="flex items-start justify-between gap-3 mb-3">
                  <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-green-50 text-green-700"><i class="fa-solid fa-receipt text-sm"></i></span>
                    <div class="text-base font-bold">Order No: {{ $order->order_no }}</div>
                    @if (!empty($order->has_comment))
                      <span class="ml-1 inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-800 px-2 py-0.5 text-[11px] font-semibold" title="Order contains item comments">
                        <i class="fa-regular fa-comment-dots"></i> Comments
                      </span>
                    @endif
                  </div>

                  <span class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold text-white status-badge {{ $statusClasses[$order->status] ?? 'bg-gray-400' }}" data-role="status-badge">
                    {{ ucfirst($order->status) }}
                  </span>
                </div>

                <div class="flex flex-col gap-2 mb-2.5">
                    @foreach ($visible as $item)
                        <div class="flex items-center gap-2">
                            <img src="{{ asset($item->product->image ?? 'assets/images/logo/logo.png') }}"
                                 alt="{{ $item->name }}"
                                 class="w-9 h-9 rounded-lg object-cover" />
                            <span>{{ $item->qty }} × {{ $item->name }}</span>
                        </div>
                    @endforeach
                     @if ($more > 0)
                    <div class="text-xs text-gray-500">+{{ $more }} more item{{ $more > 1 ? 's' : '' }}…</div>
                  @endif
                </div>

                <div class="flex justify-between text-sm text-gray-600">
                    <span>{{ \Carbon\Carbon::parse($order->pickup_date)->format('d/m/Y') }} at {{ $order->pickup_time }}</span>
                      <span class="inline-flex items-center gap-1 font-semibold text-gray-800"><i class="fa-solid fa-coins"></i> {{ number_format($order->total) }} MMK</span>
                    <a href="{{ route('customer.die_in.order_detail', ['id' => $order->id]) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-green-700 text-white text-sm font-bold hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-600">
                      <i class="fa-solid fa-up-right-from-square"></i>
                      Order details
                    </a>
                </div>
            </div> --}}
            @php
    $items   = $order->items;
    $status  = strtolower($order->status);
    $visible = $items->take(2);
    $more    = max($items->count() - 2, 0);

    // normalize status for your color map (handles both `cancel` and `canceled`)
    $statusKey = $status === 'cancel' ? 'canceled' : $order->status;

    $isAddon = !is_null($order->parent_order_id);
    $typeBadgeClass = $isAddon ? 'bg-indigo-600' : 'bg-teal-600';
@endphp

<div class="bg-white rounded-xl p-4 mb-4 shadow-md"
     onclick="window.location.href='{{ route('customer.die_in.order_detail', ['id' => $order->id]) }}'"
     data-order-id="{{ $order->id }}">

  <div class="flex items-start justify-between gap-3 mb-3">
    <div class="flex items-center gap-2 flex-wrap">
      <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-green-50 text-green-700">
        <i class="fa-solid fa-receipt text-sm"></i>
      </span>

      <div class="text-base font-bold">Order No: {{ $order->order_no }}</div>

      {{-- Type badge: Main or Add-On --}}
      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold text-white {{ $typeBadgeClass }}">
        @if($isAddon)
          <i class="fa-solid fa-plus"></i> Add-On
          @php
            $parentNo = $order->parent?->order_no ?? $order->parent_order_id;
          @endphp
          @if($parentNo)
            <span class="ml-1 opacity-90">of #{{ $parentNo }}</span>
          @endif
        @else
          <i class="fa-solid fa-layer-group"></i> Main
        @endif
      </span>

      @if (!empty($order->has_comment))
        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-800 px-2 py-0.5 text-[11px] font-semibold"
              title="Order contains item comments">
          <i class="fa-regular fa-comment-dots"></i> Comments
        </span>
      @endif
    </div>

    <span
      class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold text-white status-badge {{ $statusClasses[$statusKey] ?? 'bg-gray-400' }}"
      data-role="status-badge">
      {{ ucfirst($status) }}
    </span>
  </div>

  {{-- Items preview (unchanged) --}}
  <div class="flex flex-col gap-2 mb-2.5">
    @foreach ($visible as $item)
      <div class="flex items-center gap-2">
        <img src="{{ asset($item->product->image ?? 'assets/images/logo/logo.png') }}"
             alt="{{ $item->name }}"
             class="w-9 h-9 rounded-lg object-cover" />
        <span>{{ $item->qty }} × {{ $item->name }}</span>
      </div>
    @endforeach
    @if ($more > 0)
      <div class="text-xs text-gray-500">+{{ $more }} more item{{ $more > 1 ? 's' : '' }}…</div>
    @endif
  </div>

  <div class="flex justify-between items-center text-sm text-gray-600">
    <span>{{ \Carbon\Carbon::parse($order->pickup_date)->format('d/m/Y') }} at {{ $order->pickup_time }}</span>
    <span class="inline-flex items-center gap-1 font-semibold text-gray-800">
      <i class="fa-solid fa-coins"></i> {{ number_format($order->total) }} MMK
    </span>
    <a href="{{ route('customer.die_in.order_detail', ['id' => $order->id]) }}"
       class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-green-700 text-white text-sm font-bold hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-600">
      <i class="fa-solid fa-up-right-from-square"></i>
      Order details
    </a>
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
</html>
