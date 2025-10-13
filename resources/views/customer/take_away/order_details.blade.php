<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('assets/images/logo/logo.png') }}" type="image/png">
    <title>Order Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/34ce5b6af8.js" crossorigin="anonymous"></script>

    <style>
      html, body { height: 100%; overflow: hidden; }
      body { font-family: "Inter", sans-serif; }
      .no-scrollbar::-webkit-scrollbar { display: none; }
      .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
      /* Vertical timeline connector */
      .timeline-line { position: absolute; left: 5px; top: 24px; bottom: -20px; width: 2px; background-color: #3c8750; }
      .timeline-item:last-child .timeline-line { height: 0; }
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

      $allStatuses = ['pending','confirmed','preparing','delivered','eating','done','canceled'];
      $currentStatus = $order->status;
      $statusTimestamps = $order->status_timestamps ?? [];

      // Which steps to render initially
      $steps = ['pending','confirmed','preparing','delivered'];
      if (in_array($currentStatus, ['eating','done'])) { $steps[] = 'eating'; $steps[] = 'done'; }
      if ($currentStatus === 'canceled') { $steps = ['canceled']; }
      $cols = count($steps) === 1 ? 'grid-cols-1' : (count($steps) === 6 ? 'grid-cols-6' : 'grid-cols-4');
    @endphp

    @php
      // ===== Payment math (prefer snapshots; otherwise compute from active taxes) =====
      $computedSubtotal = $order->items->sum(fn($it) => (int)$it->price * (int)$it->qty);

      $subtotal = $order->subtotal ?? $computedSubtotal;

      // Try snapshot first (what was applied at checkout)
      $taxLabel   = $order->tax_name_snapshot ?? null;          // e.g. "VAT 7% + Service 5%"
      $taxPercent = $order->tax_percent_snapshot ?? null;       // e.g. 12.00
      $taxAmount  = $order->tax_amount ?? null;
      $grandTotal = $order->total ?? null;

      // If snapshot missing, compute from active taxes so the page still shows something sensible
      if (is_null($taxLabel) || is_null($taxPercent) || is_null($taxAmount) || is_null($grandTotal)) {
          $activeTaxes = \App\Models\Tax::where('is_active', true)
                          ->orderByDesc('is_default')
                          ->orderBy('name')
                          ->get();

          $taxLabel   = $taxLabel   ?? $activeTaxes->map(fn($t) => "{$t->name} {$t->percent}%")->join(' + ');
          $taxPercent = $taxPercent ?? (float)$activeTaxes->sum('percent');
          $taxAmount  = $taxAmount  ?? (int) round($subtotal * ($taxPercent / 100));
          $grandTotal = $grandTotal ?? ($subtotal + $taxAmount);
      }
    @endphp

    <!-- NAV -->
    <nav class="fixed inset-x-0 top-0 z-50 bg-white border-b border-green-200">
      <div class="flex h-14 items-center justify-between px-4 sm:px-6">
        <div class="flex items-center space-x-3">
          <div class="h-9 w-9 rounded-full overflow-hidden">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo" class="w-full h-full object-cover"/>
          </div>
          <span class="text-xl font-extrabold text-green-700">Hello!</span>
        </div>

        <div class="flex items-center space-x-6">
          <a href="{{ route('customer.take_away.home') }}" class="flex items-center hover:text-green-600 transition-colors duration-200">
            <i class="fa-solid fa-house"></i>
            <span class="hidden sm:inline ml-2">Home</span>
          </a>

          <a href="{{ route('customer.take_away.order_history') }}" class="flex items-center text-green-700 hover:text-green-600 transition-colors duration-200 active">
            <i class="fa-regular fa-rectangle-list"></i>
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

    <!-- Content -->
    <div class="flex-1 overflow-y-auto pt-14 pb-24 no-scrollbar" id="orderDetailsRoot">
      <div class="px-4 sm:px-6">
        <!-- Header -->
        <header class="sticky bg-white border-b border-gray-200 -mx-4 sm:-mx-6 px-4 sm:px-6 py-4 z-10 mb-5">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
              <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-green-50 text-green-700"><i class="fa-solid fa-receipt"></i></span>
              <div>
                <div class="text-xl font-bold text-green-700">Order Details</div>
                <div class="text-sm text-gray-600">Order No: <span class="font-semibold" id="orderNo">{{ $order->order_no }}</span></div>
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
              <a href="{{ route('customer.take_away.order_history') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-green-700 text-white text-sm font-bold hover:bg-green-800">
                <i class="fa-solid fa-arrow-left"></i> Back to Orders
              </a>
            </div>
          </div>
        </header>

        <!-- Status + Payment Summary Card -->
        <section class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 mt-4 ">
          <div class="flex items-start justify-between gap-3 mb-4">
            @php $palette = $statusPalette[$currentStatus] ?? ['chip' => 'bg-gray-400', 'border' => 'border-gray-300']; @endphp
            <div class="flex items-center gap-2">
              <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-bold text-white {{ $palette['chip'] }}" id="statusChip">{{ ucfirst($currentStatus) }}</span>
              <div class="text-sm text-gray-600 hidden sm:block">Updated {{ optional($order->updated_at)->diffForHumans() }}</div>
            </div>
            <div class="text-right">
              <div class="text-sm text-gray-600">Total</div>
              <!-- use computed/snapshotted grandTotal -->
              <div class="text-lg sm:text-xl font-bold">{{ number_format($grandTotal) }} MMK</div>
            </div>
          </div>

          <!-- Horizontal stepper (md+) -->
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
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors duration-200 step-dot {{ $isDone ? 'bg-green-700 text-white' : 'bg-gray-200 text-gray-500' }}" data-status="{{ $status }}">{{ $i+1 }}</div>
                    <div class="flex flex-col">
                      <div class="text-sm font-semibold step-title" data-status="{{ $status }}">{{ ucfirst($status) }}</div>
                      <div class="text-xs text-gray-500" id="ts-{{ $status }}-h">{{ isset($statusTimestamps[$status]) ? \Carbon\Carbon::parse($statusTimestamps[$status])->format('d/m/Y | h:i A') : '' }}</div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          <!-- Vertical timeline (mobile) -->
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

          <!-- Estimated pickup footer inside card -->
          <div class="flex justify-between items-center mt-4 bg-green-700 text-white rounded-xl px-4 py-3">
            <span class="font-semibold">Estimated Pickup Time</span>
            <span id="pickup-time-value" class="font-bold">{{ \Carbon\Carbon::parse($order->pickup_date)->format('d/m/Y') }} | {{ $order->pickup_time }}</span>
          </div>
        </section>

        <!-- Items -->
        <section class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 mt-4">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-bold text-gray-800">Items</h3>
            <div class="text-sm text-gray-500">{{ $order->items->count() }} item{{ $order->items->count() > 1 ? 's' : '' }}</div>
          </div>

          <div class="divide-y">
            @foreach($order->items as $item)
              <div class="py-3 flex items-start justify-between gap-3">
                <div class="flex items-start gap-3 min-w-0">
                  <img loading="lazy" src="{{ asset($item->product->image ?? 'assets/images/logo/logo.png') }}" alt="{{ $item->name }}" class="w-12 h-12 rounded-lg object-cover" />
                  <div class="min-w-0">
                    <div class="font-semibold text-sm sm:text-base truncate">{{ $item->qty }} × {{ $item->name }}</div>
                    @if($item->comment)
                      <div class="mt-1 text-xs text-amber-800 bg-amber-50 inline-flex items-center gap-1 px-2 py-1 rounded">
                        <i class="fa-regular fa-comment-dots"></i> {{ $item->comment }}
                      </div>
                    @endif
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-semibold">{{ number_format($item->price * $item->qty) }} MMK</div>
                  <div class="text-xs text-gray-500">@ {{ number_format($item->price) }}</div>
                </div>
              </div>
            @endforeach
          </div>

          <!-- Payment summary (Subtotal / Tax / Total) -->
          <div class="mt-4 border-t pt-3 space-y-2">
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-600">Subtotal</span>
              <span class="font-semibold">{{ number_format($subtotal) }} MMK</span>
            </div>

            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-600">
                Tax
                @if(!empty($taxLabel))
                  <span class="ml-1 inline-flex items-center gap-1 rounded-full bg-green-50 text-green-700 px-2 py-0.5 text-[11px] font-semibold">
                    <i class="fa-solid fa-percent text-[10px]"></i>
                    {{ $taxLabel }}
                  </span>
                @endif
              </span>
              <span class="font-semibold">{{ number_format($taxAmount) }} MMK</span>
            </div>

            <div class="flex items-center justify-between pt-2 border-t">
              <span class="text-base font-bold">Total</span>
              <span class="text-lg sm:text-xl font-extrabold">{{ number_format($grandTotal) }} MMK</span>
            </div>
          </div>
        </section>

      </div>
    </div>

    <!-- Sticky action bar -->
    <div class="fixed bottom-0 inset-x-0 bg-white border-t border-gray-200 px-4 sm:px-6 py-3">
      <div class="max-w-5xl mx-auto flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-between">
        <div class="text-sm text-gray-600">Need help? <a href="#" class="font-semibold text-green-700 hover:underline">Thanks You</a></div>
        <div class="flex items-center gap-2">
          <a href="{{ route('customer.take_away.order_history') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-700 text-white text-sm font-bold hover:bg-green-800"><i class="fa-solid fa-list"></i> Order history</a>
        </div>
      </div>
    </div>

    <!-- Echo / Pusher realtime updates -->
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.3/echo.iife.js"></script>
    <script>
      const STATUS_CLASSES = {
        pending:   'bg-yellow-400',
        confirmed: 'bg-blue-500',
        preparing: 'bg-amber-500',
        delivered: 'bg-green-600',
        eating:    'bg-purple-500',
        done:      'bg-gray-600',
        canceled:  'bg-red-600',
      };
      const ALL_STATUSES = ['pending','confirmed','preparing','delivered','eating','done','canceled'];

      function formatTS(dtStr){
        if(!dtStr) return '';
        const dt = new Date(dtStr);
        if (isNaN(dt.getTime())) return dtStr; // fallback if server string isn't ISO
        return dt.toLocaleDateString('en-GB') + ' | ' + dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
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
          const st = el.getAttribute('data-status');
          const idx = ALL_STATUSES.indexOf(st);
          el.classList.toggle('bg-green-700', idx <= currentIdx);
          el.classList.toggle('text-white', idx <= currentIdx);
          el.classList.toggle('bg-gray-200', idx > currentIdx);
          el.classList.toggle('text-gray-500', idx > currentIdx);
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

    //   function updatePickup(dateStr, timeStr){
    //     const lbl = document.getElementById('pickup-time-value');
    //     if (!lbl) return;
    //     const safe = (dateStr && timeStr) ? `${dateStr} ${timeStr}` : null;
    //     lbl.textContent = formatTS(safe || lbl.textContent);
    //   }
    function updatePickup(dateStr, timeStr) {
    const lbl = document.getElementById('pickup-time-value');
    if (!lbl) return;

    let datePart = '';
    if (dateStr && dateStr.includes('T')) {
      // ISO like 2025-08-07T17:30:00.000000Z
      const d = new Date(dateStr);
      datePart = isNaN(d) ? dateStr.split('T')[0] : d.toLocaleDateString('en-GB');
      // We already have time embedded in the ISO; prefer showing the nice timeStr if given
      lbl.textContent = `${datePart} | ${timeStr ?? d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}`;
      return;
    }

    // Expecting YYYY-MM-DD
    if (dateStr) {
      const [y, m, d] = dateStr.split('-').map(Number);
      const dt = new Date(y, (m || 1) - 1, d || 1);
      datePart = isNaN(dt) ? dateStr : dt.toLocaleDateString('en-GB');
    }

    lbl.textContent = `${datePart} | ${timeStr ?? ''}`.trim();
  }


      // Copy order number
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

      // Realtime
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
