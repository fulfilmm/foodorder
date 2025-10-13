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
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/34ce5b6af8.js" crossorigin="anonymous"></script>


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
     @php
      $initialDistinct = count(session('cart', []));
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

          {{-- <div class="relative pl-6 mb-6">
            <div class="timeline-item flex items-start mb-6 relative">
              <div
                class="absolute -left-1.5 top-0 w-6 h-6 flex items-center justify-center rounded-full bg-green-700 text-white z-10"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="3"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <div class="timeline-line"></div>
              <div class="ml-6 flex flex-col">
                <span class="font-semibold text-base sm:text-lg"
                  >Order Confirmed</span
                >
                <div class="flex items-center text-gray-500 text-sm mt-1">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 mr-1"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                  >
                    <path
                      d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"
                    />
                    <path d="M12 6v6h4v-2h-2V6h-2z" />
                  </svg>
                  <span>22/04/2024 at 10 AM</span>
                </div>
              </div>
            </div>

            <div class="timeline-item flex items-start mb-6 relative">
              <div
                class="absolute -left-1.5 top-0 w-6 h-6 flex items-center justify-center rounded-full bg-green-700 text-white z-10"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="3"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <div class="timeline-line"></div>
              <div class="ml-6 flex flex-col">
                <span class="font-semibold text-base sm:text-lg"
                  >Order Prepared</span
                >
                <div class="flex items-center text-gray-500 text-sm mt-1">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 mr-1"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                  >
                    <path
                      d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"
                    />
                    <path d="M12 6v6h4v-2h-2V6h-2z" />
                  </svg>
                  <span>Soon</span>
                </div>
              </div>
            </div>

            <div class="timeline-item flex items-start relative">
              <div
                class="absolute -left-1.5 top-0 w-6 h-6 flex items-center justify-center rounded-full bg-gray-500 text-white z-10"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="3"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
              <div class="ml-6 flex flex-col">
                <span class="font-semibold text-base sm:text-lg"
                  >Order Ready to Pickup</span
                >
              </div>
            </div>
          </div> --}}

          {{-- <div
            class="flex justify-between py-6 bg-green-700 text-white text-base rounded-b-xl -mx-4 sm:-mx-6 px-4 sm:px-6"
            id="pickup-time-in-card"
          >
            <span class="font-semibold text-sm sm:text-base"
              >Estimated Pickup Time:</span
            >
            <span class="font-bold text-base sm:text-lg"
              >22/04/2024 | 2:30 PM</span
            >
          </div> --}}
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
    {{-- <script>
        Pusher.logToConsole = true;

        window.Echo = new Echo({
          broadcaster: 'pusher',
          key: '{{ config("broadcasting.connections.pusher.key") }}',
          cluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}',
          forceTLS: true
        });

        Echo.channel('orders')
          .listen('.OrderStatusUpdated', e => {
            const status = e.order.status;
            console.log(e)
            updateTimeline(status, e.order.status_timestamps);
          });

        function updateTimeline(newStatus, updatedTimestamps) {
            console.log(updatedTimestamps)
          // Optional: update pickup time if changed
          document.getElementById('pickup-time-in-card').querySelector('span:last-child')
            .textContent = updatedTimestamps.pickup_date + ' | ' + updatedTimestamps.pickup_time;

          // Rebuild timeline dynamically
          const timeline = document.querySelector('.relative.pl-6.mb-6');
          timeline.innerHTML = ''; // clear

          const allStatuses = ['pending','confirmed','preparing','delivered','eating','done','canceled'];
          let statusesToShow = ['pending','confirmed','preparing','delivered'];
          if (['eating','done'].includes(newStatus)) {
            statusesToShow.push('eating','done');
          }
          if (newStatus === 'canceled') {
            statusesToShow = ['canceled'];
          }
          const currentIndex = allStatuses.indexOf(newStatus);

          statusesToShow.forEach((st, i) => {
            const done = allStatuses.indexOf(st) <= currentIndex;
            const bg = done ? 'bg-green-700' : 'bg-gray-500';
            const time = updatedTimestamps[st] || null;

            const item = document.createElement('div');
            item.className = 'timeline-item flex items-start mb-6 relative';

            const dot = document.createElement('div');
            dot.className = `absolute -left-1.5 top-0 w-6 h-6 flex items-center justify-center rounded-full ${bg} text-white z-10`;
            dot.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" ...><polyline points="20 6 9 17 4 12"/></svg>`;
            item.appendChild(dot);

            if (i < statusesToShow.length - 1) {
              const line = document.createElement('div');
              line.className = 'timeline-line';
              item.appendChild(line);
            }

            const content = document.createElement('div');
            content.className = 'ml-6 flex flex-col';
            content.innerHTML = `<span class="font-semibold text-base sm:text-lg">${st.charAt(0).toUpperCase()+st.slice(1)}</span>`;
            if (time) {
              content.innerHTML += `
                <div class="flex items-center text-gray-500 text-sm mt-1">
                  <svg ... class="h-4 w-4 mr-1">...</svg>
                  <span>${time}</span>
                </div>`;
            }
            item.appendChild(content);

            timeline.appendChild(item);
          });
        }
      </script> --}}
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

        // function createTimelineItem(status, isDone, timestamp, isLast) {
        //   const wrapper = document.createElement('div');
        //   wrapper.className = 'timeline-item flex items-start mb-6 relative';

        //   const dot = document.createElement('div');
        //   dot.className = `absolute -left-1.5 top-0 w-6 h-6 flex items-center justify-center rounded-full ${isDone ? 'bg-green-700' : 'bg-gray-500'} text-white z-10`;
        //   dot.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>`;
        //   wrapper.appendChild(dot);

        //   if (!isLast) {
        //     const line = document.createElement('div');
        //     line.className = 'timeline-line';
        //     wrapper.appendChild(line);
        //   }

        //   const content = document.createElement('div');
        //   content.className = 'ml-6 flex flex-col';
        //   content.innerHTML = `<span class="font-semibold text-base sm:text-lg">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;

        //   if (timestamp) {
        //     content.innerHTML += `
        //       <div class="flex items-center text-gray-500 text-sm mt-1">
        //         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
        //           <path d="M12 2a10 10 0 100 20 10 10 0 000-20zm0 18a8 8 0 110-16 8 8 0 010 16z"/>
        //           <path d="M12 6v6h4v-2h-2V6h-2z"/>
        //         </svg>
        //         <span>${formatDateTime(timestamp)}</span>
        //       </div>`;
        //   }

        //   wrapper.appendChild(content);
        //   return wrapper;
        // }

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
</html>
