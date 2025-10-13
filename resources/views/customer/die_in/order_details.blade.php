{{-- <!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cart Page</title>
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap"
      rel="stylesheet"
    />
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
      html,
      body {
        height: 100%;
        overflow: hidden;
      }

      body {
        font-family: "Inter", sans-serif;
      }

      .no-scrollbar::-webkit-scrollbar {
        display: none;
      }
      .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
      }

      /* UPDATED: Timeline Line CSS */
      .timeline-line {
        position: absolute;
        left: 5px; /* Aligns with the center of the 24px checkmark circle */
        top: 24px; /* Start below the checkmark circle */
        bottom: -20px; /* End above the checkmark circle of the next item */
        width: 2px;
        background-color: #3c8750; /* green-300 */
      }
      /* Ensure the last timeline item's line is shorter */
      .timeline-item:last-child .timeline-line {
        height: 0; /* No line for the last item */
      }
    </style>
  </head>
  <body class="bg-white text-gray-800 flex flex-col h-screen">
    <nav class="fixed inset-x-0 top-0 z-50 bg-white border-b border-green-200">
      <div class="flex h-14 items-center justify-between px-4 sm:px-6">
        <div class="flex items-center space-x-3">
          <div class="h-9 w-9 rounded-full bg-white overflow-hidden">
            <img
              src="/images/logo/logo.png"
              alt="Logo"
              class="w-full h-full object-cover"
            />
          </div>
          <span class="text-xl font-extrabold text-green-700">Hello!</span>
        </div>

        <div class="flex items-center space-x-6">
          <a
            href="#"
            class="flex items-center hover:text-green-600 transition-colors duration-200"
          >
            <svg class="h-6 w-6 fill-current" viewBox="0 0 24 24">
              <path
                d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"
              />
            </svg>
            <span class="hidden sm:inline ml-2">Home</span>
          </a>

          <a
            href="#"
            class="flex items-center text-green-700 hover:text-green-600 transition-colors duration-200"
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
              fill="currentColor"
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

    <div class="flex-1 overflow-y-auto pt-14 pb-4">
        <div class="px-4 sm:px-6">
          <h1
            class="text-2xl font-bold py-4 text-green-700 sticky top-0 bg-white z-10 border-b border-gray-200 -mx-4 sm:-mx-6 px-4 sm:px-6"
          >
            Order Details
          </h1>

          <div class="bg-white rounded-xl px-4 sm:p-6 mb-4 shadow-lg mt-4">
            <div class="flex items-center mb-4">

              <div class="flex items-center text-gray-600 text-sm sm:text-base">
                  <h2 class="text-lg sm:text-xl font-bold text-gray-700 mr-4">
                    {{$order->table->name}}
                  </h2>
                  <div class="flex items-center text-gray-600 text-sm sm:text-base">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5 mr-1"
                      viewBox="0 0 24 24"
                      fill="currentColor"
                    >
                      <path
                        d="M20 6h-4V4c0-1.103-.897-2-2-2h-4c-1.103 0-2 .897-2 2v2H4c-1.103 0-2 .897-2 2v11c0 1.103.897 2 2 2h16c1.103 0 2-.897 2-2V8c0-1.103-.897-2-2-2zm-6-2h-4v2h4V4zm6 15H4V8h16v11z"
                      />
                    </svg>
                  </div>
                  <h2 class="text-lg sm:text-xl font-bold text-gray-700 mr-4">
                      Order No: {{ $order->order_no }}
                  </h2>
              </div>
            </div>

            <h3 class="text-base sm:text-lg font-bold text-gray-700 mb-2">
              Payment Details
            </h3>
            <div class="space-y-1 text-sm sm:text-base mb-6">
              <div class="flex justify-between">
                  <span class="text-gray-600">Subtotal</span>
                  <span class="font-semibold">{{ number_format($order->total) }} MMK</span>
              </div>
              <div class="flex justify-between font-bold text-lg sm:text-xl">
                  <span>Total</span>
                  <span>{{ number_format($order->total) }} MMK</span>
              </div>
            </div>
            @php
            $allStatuses = ['pending', 'confirmed', 'preparing', 'delivered', 'eating', 'done', 'canceled'];
            $currentStatus = $order->status;
            $statusTimestamps = $order->status_timestamps ?? [];

            // Default statuses to show
            $statuses = ['pending', 'confirmed', 'preparing', 'delivered'];

            // If status reached eating or done, show 'eating' and 'done'
            if (in_array($currentStatus, ['eating', 'done'])) {
                $statuses[] = 'eating';
                $statuses[] = 'done';
            }

            // If status is only canceled, show just that
            if ($currentStatus === 'canceled') {
                $statuses = ['canceled'];
            }

            $currentStatusIndex = array_search($currentStatus, $allStatuses);
        @endphp


        <div id="order-timeline" class="relative pl-6 mb-6">
            @foreach ($statuses as $index => $status)
                @php
                    $isCompleted = array_search($status, $statuses) <= array_search($currentStatus, $statuses);
                    $bgClass = $isCompleted ? 'bg-green-700' : 'bg-gray-500';
                    $time = $statusTimestamps[$status] ?? null;
                @endphp

                <div class="timeline-item flex items-start mb-6 relative">
                    <div class="absolute -left-1.5 top-0 w-6 h-6 flex items-center justify-center rounded-full {{ $bgClass }} text-white z-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    </div>

                    @if ($index < count($statuses) - 1)
                        <div class="timeline-line"></div>
                    @endif

                    <div class="ml-6 flex flex-col">
                        <span class="font-semibold text-base sm:text-lg">
                            {{ ucfirst($status) }}
                        </span>
                        @if ($time)
                            <div class="flex items-center text-gray-500 text-sm mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"/>
                                    <path d="M12 6v6h4v-2h-2V6h-2z"/>
                                </svg>
                                <span>{{ \Carbon\Carbon::parse($time)->format('d/m/Y | h:i A') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
            <div class="flex justify-between py-6 bg-green-700 text-white text-base rounded-b-xl -mx-4 sm:-mx-6 px-4 sm:px-6" id="pickup-time-in-card">
              <span class="font-semibold text-sm sm:text-base">Estimated Pickup Time:</span>
              <span id="pickup-time-value" class="font-bold text-base sm:text-lg">
                  {{ \Carbon\Carbon::parse($order->pickup_date)->format('d/m/Y') }} | {{ $order->pickup_time }}
              </span>
          </div>

          </div>
        </div>
      </div>
      <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.3/echo.iife.js"></script>
    <script>
        const allStatuses = ['pending', 'confirmed', 'preparing', 'delivered', 'eating', 'done', 'canceled'];

        function getStatusesToShow(currentStatus) {
          if (currentStatus === 'canceled') return ['canceled'];
          const base = ['pending', 'confirmed', 'preparing', 'delivered'];
          if (['eating', 'done'].includes(currentStatus)) base.push('eating', 'done');
          return base;
        }

        function formatDateTime(dateTimeStr) {
          const dt = new Date(dateTimeStr);
          return dt.toLocaleDateString('en-GB') + ' | ' + dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        }
        function createTimelineItem(status, isDone, timestamp, isLast) {
  const wrapper = document.createElement('div');
  wrapper.className = 'timeline-item flex items-start mb-6 relative';

  const dot = document.createElement('div');
  dot.className = `absolute -left-1.5 top-0 w-6 h-6 flex items-center justify-center rounded-full ${isDone ? 'bg-green-700' : 'bg-gray-500'} text-white z-10`;
  dot.innerHTML = `
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
         stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
      <polyline points="20 6 9 17 4 12" />
    </svg>`;
  wrapper.appendChild(dot);

  // timeline-line (vertical line between dots)
  if (!isLast) {
    const line = document.createElement('div');
    line.className = 'timeline-line ';
    wrapper.appendChild(line);
  }

  const content = document.createElement('div');
  content.className = 'ml-6 flex flex-col';

  // status title
  const statusTitle = document.createElement('span');
  statusTitle.className = 'font-semibold text-base sm:text-lg';
  statusTitle.textContent = status.charAt(0).toUpperCase() + status.slice(1);
  content.appendChild(statusTitle);

  // timestamp
  if (timestamp) {
    const timeWrap = document.createElement('div');
    timeWrap.className = 'flex items-center text-gray-500 text-sm mt-1';
    timeWrap.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8
          s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"/>
        <path d="M12 6v6h4v-2h-2V6h-2z"/>
      </svg>
      <span>${formatDateTime(timestamp)}</span>
    `;
    content.appendChild(timeWrap);
  }

  wrapper.appendChild(content);
  return wrapper;
}
function updateTimeline(currentStatus, timestamps) {
          const timeline = document.getElementById('order-timeline');
          timeline.innerHTML = '';

          const statusesToShow = getStatusesToShow(currentStatus);
          const currentIndex = allStatuses.indexOf(currentStatus);

          statusesToShow.forEach((status, index) => {
            const isDone = allStatuses.indexOf(status) <= currentIndex;
            const isLast = index === statusesToShow.length - 1;
            const time = timestamps[status] || null;

            const item = createTimelineItem(status, isDone, time, isLast);
            timeline.appendChild(item);
          });
        }

        function updatePickupTime(date, time) {
          const pickupText = formatDateTime(`${date} ${time}`);
          document.getElementById('pickup-time-value').textContent = pickupText;
        }

        // Setup Echo (Laravel Echo with Pusher)
        Pusher.logToConsole = true;
        window.Echo = new Echo({
          broadcaster: 'pusher',
          key: '{{ config("broadcasting.connections.pusher.key") }}',
          cluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}',
          forceTLS: true
        });

        Echo.channel('orders')
          .listen('.OrderStatusUpdated', e => {
            const { status, status_timestamps, pickup_date, pickup_time } = e.order;
            console.log(pickup_date,pickup_time)
            updateTimeline(status, status_timestamps);
            updatePickupTime(pickup_date, pickup_time);
          });

        // Initial load (optional: insert PHP-generated status/timestamps here)
        // updateTimeline('confirmed', {
        //   pending: '2025-06-16 09:00',
        //   confirmed: '2025-06-16 09:10',
        //   preparing: null
        // });
      </script>
  </body>
</html> --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Order Details</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://kit.fontawesome.com/34ce5b6af8.js" crossorigin="anonymous"></script>
  <style>
    html,body{height:100%;overflow:hidden}
    body{font-family:"Inter",sans-serif}
    .no-scrollbar::-webkit-scrollbar{display:none}
    .no-scrollbar{-ms-overflow-style:none;scrollbar-width:none}
    .timeline-line{position:absolute;left:5px;top:24px;bottom:-20px;width:2px;background:#3c8750}
    .timeline-item:last-child .timeline-line{height:0}
  </style>
</head>
<body class="bg-white text-gray-800 flex flex-col h-screen">
  @php
    $initialDistinct = count(session('cart', []));
    $statusPalette = [
      'pending'   => ['chip' => 'bg-yellow-400', 'border' => 'border-yellow-400'],
      'confirmed' => ['chip' => 'bg-blue-500',   'border' => 'border-blue-500'],
      'preparing' => ['chip' => 'bg-amber-500',  'border' => 'border-amber-500'],
      'delivered' => ['chip' => 'bg-green-600',  'border' => 'border-green-600'],
      'eating'    => ['chip' => 'bg-purple-500', 'border' => 'border-purple-500'],
      'done'      => ['chip' => 'bg-gray-600',   'border' => 'border-gray-400'],
      'canceled'  => ['chip' => 'bg-red-600',    'border' => 'border-red-600'],
    ];
    $allStatuses       = ['pending','confirmed','preparing','delivered','eating','done','canceled'];
    $currentStatus     = $order->status;
    $statusTimestamps  = $order->status_timestamps ?? [];
    // Which steps to render initially
    $steps = ['pending','confirmed','preparing','delivered'];
    if (in_array($currentStatus,['eating','done'])) { $steps[]='eating'; $steps[]='done'; }
    if ($currentStatus==='canceled') { $steps=['canceled']; }
    $cols = count($steps)===1 ? 'grid-cols-1' : (count($steps)===6 ? 'grid-cols-6' : 'grid-cols-4');
  @endphp

  <!-- NAV -->
  <nav class="fixed inset-x-0 top-0 z-50 bg-white border-b border-green-200">
    <div class="flex h-14 items-center justify-between px-4 sm:px-6">
      <div class="flex items-center gap-3">
        <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo" class="h-9 w-9 rounded-full object-cover"/>
        <span class="text-xl font-extrabold text-green-700">Hello!</span>
      </div>
      <div class="flex items-center gap-6">
        <a href="{{ route('customer.die_in.home') }}" class="flex items-center hover:text-green-600 transition">
          <i class="fa-solid fa-house text-lg"></i><span class="hidden sm:inline ml-2">Home</span>
        </a>
        <a href="{{ route('customer.die_in.order_history') }}" class="flex items-center hover:text-green-600 transition">
          <i class="fa-regular fa-rectangle-list text-lg"></i><span class="hidden sm:inline ml-2">Orders</span>
        </a>
        <a href="{{ route('customer.die-in.cart') }}" class="hover:text-green-600 transition inline-flex items-center gap-2">
          <span class="relative inline-block">
            <i class="fa-solid fa-cart-shopping text-xl"></i>
            <span id="nav-cart-count" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold px-1.5 py-0.5 rounded-full leading-none">{{ $initialDistinct }}</span>
          </span>
          <span class="hidden sm:inline">Cart</span>
        </a>
      </div>
    </div>
  </nav>

  <!-- PAGE -->
  <div class="flex-1 overflow-y-auto no-scrollbar pt-14 pb-24" id="orderDetailsRoot">
    <div class="px-4 sm:px-6">
      <!-- Header -->
      <header class="sticky bg-white border-b border-gray-200 -mx-4 sm:-mx-6 px-4 sm:px-6 py-4 z-10 mb-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-green-50 text-green-700">
              <i class="fa-solid fa-receipt"></i>
            </span>
            <div>
              <div class="text-xl font-bold text-green-700">Order Details</div>
              <div class="text-sm text-gray-600">
                Order No: <span class="font-semibold" id="orderNo">{{ $order->order_no }}</span>
              </div>
            </div>

            @if(!empty($order->has_comment))
              <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-800 px-2 py-0.5 text-[11px] font-semibold" title="Order contains item comments">
                <i class="fa-regular fa-comment-dots"></i> Comments
              </span>
            @endif
          </div>

          <div class="flex items-center gap-2">
            <button id="copyOrderNo" class="inline-flex items-center gap-2 px-3 py-2 rounded-full border border-gray-300 text-sm font-semibold hover:bg-gray-50">
              <i class="fa-regular fa-copy"></i> Copy No.
            </button>
            <a href="{{ route('customer.die_in.order_history') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-green-700 text-white text-sm font-bold hover:bg-green-800">
              <i class="fa-solid fa-arrow-left"></i> Back to Orders
            </a>
          </div>
        </div>
      </header>

      <!-- Card -->
      <section class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 mt-4">
        <!-- Top row: table + status + amounts -->
        @php $palette = $statusPalette[$currentStatus] ?? ['chip'=>'bg-gray-400','border'=>'border-gray-300']; @endphp
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between mb-4">
          <div class="flex items-center flex-wrap gap-x-3 gap-y-2">
            @if($order->table)
              <span class="inline-flex items-center gap-2 text-sm text-gray-700">
                <i class="fa-solid fa-chair"></i>
                <span class="font-semibold">Table:</span> {{ $order->table->name }}
              </span>
            @endif

            <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-bold text-white {{ $palette['chip'] }}" id="statusChip">
              {{ ucfirst($currentStatus) }}
            </span>
          </div>

          <div class="text-right">
            <div class="text-xs text-gray-500">Created</div>
            <div class="text-sm font-semibold">
              {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y | h:i A') }}
            </div>
          </div>
        </div>

        <!-- Items (compact list) -->
        <div class="border rounded-xl p-3 mb-4">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-bold text-gray-800">Items</h3>
            <div class="text-xs text-gray-500">{{ $order->items->count() }} item{{ $order->items->count() > 1 ? 's' : '' }}</div>
          </div>

          <div class="divide-y">
            @foreach($order->items as $item)
              <div class="py-2 flex items-start justify-between gap-3">
                <div class="flex items-start gap-3 min-w-0">
                  <img loading="lazy" src="{{ asset($item->product->image ?? 'assets/images/logo/logo.png') }}" alt="{{ $item->name }}" class="w-10 h-10 rounded-md object-cover"/>
                  <div class="min-w-0">
                    <div class="font-semibold text-sm truncate">{{ $item->qty }} × {{ $item->name }}</div>
                    @if($item->comment)
                      <div class="mt-1 text-[11px] text-amber-800 bg-amber-50 inline-flex items-center gap-1 px-1.5 py-0.5 rounded">
                        <i class="fa-regular fa-comment-dots"></i> {{ $item->comment }}
                      </div>
                    @endif
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-semibold">{{ number_format($item->price * $item->qty) }} MMK</div>
                  <div class="text-[11px] text-gray-500">@ {{ number_format($item->price) }}</div>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        <!-- Payment Summary -->
        <div class="grid sm:grid-cols-2 gap-4">
          <div class="border rounded-xl p-3">
            <h3 class="text-sm font-bold text-gray-800 mb-2">Payment</h3>
            <div class="text-sm text-gray-700 space-y-1">
              <div class="flex justify-between"><span>Subtotal</span><span class="font-semibold">{{ number_format($order->subtotal ?? ($order->total - $order->tax_amount)) }} MMK</span></div>
              <div class="flex justify-between"><span>Tax ({{ (int)($order->tax_percent_snapshot ?? 0) }}%)</span><span class="font-semibold">{{ number_format($order->tax_amount ?? 0) }} MMK</span></div>
              @if(!empty($order->tax_name_snapshot))
                <div class="text-xs text-gray-500">Applied: {{ $order->tax_name_snapshot }}</div>
              @endif
            </div>
            <div class="flex justify-between font-bold text-lg mt-3 border-t pt-2">
              <span>Total</span>
              <span>{{ number_format($order->total) }} MMK</span>
            </div>
          </div>

          <!-- Timeline -->
          <div class="border rounded-xl p-3">
            <h3 class="text-sm font-bold text-gray-800 mb-2">Status</h3>

            <!-- Horizontal (md+) -->
            <div class="hidden md:block">
              <div class="grid {{ $cols }} gap-4 items-start" id="stepperH">
                @php $currentIdx = array_search($currentStatus, $allStatuses); @endphp
                @foreach($steps as $i => $status)
                  @php
                    $idx = array_search($status, $allStatuses);
                    $isDone = $idx <= $currentIdx;
                  @endphp
                  <div class="relative">
                    @if($i < count($steps)-1)
                      <div class="absolute left-0 right-0 top-4 h-0.5 bg-gray-200"></div>
                    @endif
                    <div class="relative z-10 flex items-center gap-2">
                      <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors duration-200 step-dot
                                  {{ $isDone ? 'bg-green-700 text-white' : 'bg-gray-200 text-gray-500' }}"
                           data-status="{{ $status }}">{{ $i+1 }}</div>
                      <div class="flex flex-col">
                        <div class="text-sm font-semibold step-title" data-status="{{ $status }}">{{ ucfirst($status) }}</div>
                        <div class="text-xs text-gray-500" id="ts-{{ $status }}-h">
                          {{ isset($statusTimestamps[$status]) ? \Carbon\Carbon::parse($statusTimestamps[$status])->format('d/m/Y | h:i A') : '' }}
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>

            <!-- Vertical (mobile) -->
            <div class="md:hidden relative pl-6 mt-2" id="timelineV">
              @foreach($steps as $i => $status)
                @php
                  $idx = array_search($status, $allStatuses);
                  $isDone = $idx <= $currentIdx;
                @endphp
                <div class="timeline-item flex items-start mb-6 relative" data-status="{{ $status }}">
                  <div class="absolute -left-1.5 top-0 w-6 h-6 flex items-center justify-center rounded-full {{ $isDone ? 'bg-green-700 text-white' : 'bg-gray-500 text-white' }} z-10">
                    <i class="fa-solid fa-check text-xs"></i>
                  </div>
                  @if($i < count($steps)-1)
                    <div class="timeline-line"></div>
                  @endif
                  <div class="ml-6 flex flex-col">
                    <span class="font-semibold text-base">{{ ucfirst($status) }}</span>
                    @if(isset($statusTimestamps[$status]))
                      <div class="flex items-center text-gray-500 text-sm mt-1">
                        <i class="fa-regular fa-clock mr-1"></i>
                        <span id="ts-{{ $status }}-v">{{ \Carbon\Carbon::parse($statusTimestamps[$status])->format('d/m/Y | h:i A') }}</span>
                      </div>
                    @else
                      <div class="text-sm text-gray-400" id="ts-{{ $status }}-v"></div>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>

        <!-- Pickup footer -->
        <div class="flex justify-between items-center mt-4 bg-green-700 text-white rounded-xl px-4 py-3">
          <span class="font-semibold">Estimated Pickup Time</span>
          <span id="pickup-time-value" class="font-bold">
            {{ \Carbon\Carbon::parse($order->pickup_date)->format('d/m/Y') }} | {{ \Carbon\Carbon::parse($order->pickup_time)->format('h:i A') }}
          </span>
        </div>
      </section>
    </div>
  </div>

  <!-- Sticky action bar -->
  <div class="fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 px-4 sm:px-6 py-3">
    <div class="max-w-5xl mx-auto flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-between">
      <div class="text-sm text-gray-600">
        <span class="inline-flex items-center gap-1"><i class="fa-regular fa-calendar"></i>
          Order Created At : {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y | h:i A') }}
        </span>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('customer.die_in.order_history') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-700 text-white text-sm font-bold hover:bg-green-800">
          <i class="fa-solid fa-list"></i> Order history
        </a>
      </div>
    </div>
  </div>

  <!-- Realtime -->
  <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.3/echo.iife.js"></script>
  <script>
    const STATUS_CLASSES = {
      pending:'bg-yellow-400', confirmed:'bg-blue-500', preparing:'bg-amber-500',
      delivered:'bg-green-600', eating:'bg-purple-500', done:'bg-gray-600', canceled:'bg-red-600'
    };
    const ALL_STATUSES = ['pending','confirmed','preparing','delivered','eating','done','canceled'];

    function formatTS(dtStr){
      if(!dtStr) return '';
      const dt = new Date(dtStr.replace(' ', 'T'));
      if (isNaN(dt.getTime())) return dtStr;
      return dt.toLocaleDateString('en-GB') + ' | ' + dt.toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit'});
    }

    function setStatusChip(newStatus){
      const chip = document.getElementById('statusChip');
      if (!chip) return;
      Object.values(STATUS_CLASSES).forEach(c => chip.classList.remove(c));
      chip.classList.add(STATUS_CLASSES[newStatus] || 'bg-gray-400');
      chip.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
    }

    function updateHorizontalStepper(current){
      const currentIdx = ALL_STATUSES.indexOf(current);
      document.querySelectorAll('#stepperH .step-dot').forEach(el => {
        const st  = el.getAttribute('data-status');
        const idx = ALL_STATUSES.indexOf(st);
        const done = idx <= currentIdx;
        el.classList.toggle('bg-green-700', done);
        el.classList.toggle('text-white', done);
        el.classList.toggle('bg-gray-200', !done);
        el.classList.toggle('text-gray-500', !done);
      });
    }

    function updateVerticalTimeline(current){
      const currentIdx = ALL_STATUSES.indexOf(current);
      document.querySelectorAll('#timelineV [data-status]').forEach(row => {
        const st = row.getAttribute('data-status');
        const idx = ALL_STATUSES.indexOf(st);
        const dot = row.querySelector('div.w-6.h-6');
        if (!dot) return;
        dot.classList.toggle('bg-green-700', idx <= currentIdx);
        dot.classList.toggle('bg-gray-500', idx > currentIdx);
      });
    }

    function updateTimestamps(ts){
      Object.entries(ts || {}).forEach(([status, when]) => {
        const val = formatTS(when);
        const hv = document.getElementById(`ts-${status}-h`);
        const vv = document.getElementById(`ts-${status}-v`);
        if (hv) hv.textContent = val;
        if (vv) vv.textContent = val;
      });
    }

    function updatePickup(dateStr, timeStr){
      const lbl = document.getElementById('pickup-time-value');
      if (!lbl) return;
      if (!dateStr || !timeStr) return;
      const merged = new Date((dateStr + ' ' + timeStr).replace(' ', 'T'));
      if (isNaN(merged.getTime())) { lbl.textContent = `${dateStr} | ${timeStr}`; return; }
      lbl.textContent = merged.toLocaleDateString('en-GB') + ' | ' + merged.toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit'});
    }

    // Copy order no
    document.getElementById('copyOrderNo')?.addEventListener('click', () => {
      const val = document.getElementById('orderNo')?.textContent?.trim();
      if (!val) return;
      navigator.clipboard.writeText(val).then(() => {
        const btn = document.getElementById('copyOrderNo');
        const old = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied';
        setTimeout(() => btn.innerHTML = old, 1200);
      });
    });

    // Echo
    Pusher.logToConsole = false;
    window.Echo = new Echo({
      broadcaster: 'pusher',
      key: "{{ config('broadcasting.connections.pusher.key') }}",
      cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
      forceTLS: true
    });

    Echo.channel('orders').listen('.OrderStatusUpdated', e => {
      const { status, status_timestamps, pickup_date, pickup_time } = e.order || {};
      if (!status) return;
      setStatusChip(status);
      updateHorizontalStepper(status);
      updateVerticalTimeline(status);
      updateTimestamps(status_timestamps || {});
      updatePickup(pickup_date, pickup_time);
    });
  </script>
</body>
</html>
