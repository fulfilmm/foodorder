<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('assets/images/logo/logo.png') }}" type="image/png">
    <title>Dining Option</title>

    <!-- Tailwind (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet" />

    <!-- Flatpickr -->
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Icons -->
    <script src="https://kit.fontawesome.com/34ce5b6af8.js" crossorigin="anonymous"></script>

    <style>
      .flatpickr-calendar { font-family: "Inter", sans-serif; }
    </style>
  </head>
  <body class="font-sans bg-white text-gray-800 min-h-screen">
    @php
      $initialDistinct = count(session('cart', [])); // distinct product_ids
      $cart = session('cart', []);
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
          <a href="{{ route('customer.take_away.cart') }}" class="hover:text-green-600 transition inline-flex items-center gap-2">
            <span class="relative inline-block">
              <i class="fa-solid fa-cart-shopping text-xl"></i>
              <span id="nav-cart-count"
                    class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold px-1.5 py-0.5 rounded-full leading-none"
                    aria-live="polite">
                {{ $initialDistinct }}
              </span>
            </span>
            <span class="hidden sm:inline">Cart</span>
          </a>
        </div>
      </div>
    </nav>

    <main class="pt-20 p-4 mx-auto max-w-3xl">
      <h2 class="text-green-700 mb-4 text-2xl font-bold md:text-3xl">Checkout</h2>

      <form action="{{ route('customer.take_away.checkout.store') }}" method="POST" id="checkoutForm">
        @csrf
        <div class="flex flex-col gap-5">
          <!-- Dining option (kept as hidden for now) -->
          <input type="hidden" name="order_type" value="takeaway" />

          <!-- Pickup Date -->
          <div class="flex flex-col gap-2">
            <label for="pickup-date" class="font-bold text-base">Pickup Date</label>
            <div class="flex items-center bg-gray-100 rounded-lg p-3 gap-2.5">
              <i class="text-lg text-green-700">📅</i>
              <input
                type="text"
                id="pickup-date"
                name="pickup_date"
                placeholder="Select date"
                class="border-none bg-transparent text-base flex-1 outline-none shadow-none focus:ring-0"
              />
            </div>
          </div>

          <!-- Pickup Time -->
          <div class="flex flex-col gap-2">
            <label for="pickup-time" class="font-bold text-base">Pickup Time</label>
            <div class="flex items-center bg-gray-100 rounded-lg p-3 gap-2.5">
              <i class="text-lg text-green-700">⏰</i>
              <input
                type="text"
                id="pickup-time"
                name="pickup_time"
                placeholder="Select time"
                class="border-none bg-transparent text-base flex-1 outline-none shadow-none focus:ring-0"
              />
            </div>
          </div>

          <!-- Phone -->
          <div class="flex flex-col gap-2">
            <label for="phone" class="font-bold text-base">Mobile Number</label>
            <div class="flex items-center bg-gray-100 rounded-lg p-3 gap-2.5">
              <span class="text-gray-700">🇲🇲 +95</span>
              <input
                type="tel"
                id="phone"
                name="phone"
                placeholder="Enter phone number"
                class="border-none bg-transparent text-base flex-1 outline-none shadow-none focus:ring-0"
                inputmode="numeric" autocomplete="tel" pattern="[0-9]{7,12}" required aria-describedby="phoneHelp"
              />
            </div>
            <small id="phoneHelp" class="text-xs text-gray-500">Enter digits only (no leading 0). We’ll save it as +95XXXXXXXXX.</small>
          </div>

          <!-- Payment info -->
          <div class="bg-green-50 p-4 rounded-lg text-green-700 font-bold">
            <div>✔ Payment Method</div>
            <p class="text-gray-700 font-normal mt-1.5">Kindly note that payment is required in cash upon arrival.</p>
          </div>

          <!-- 🔥 Totals card with ALL active taxes (read-only) -->
          <div class="border border-gray-100 rounded-xl p-4 bg-white shadow-sm">
            <div class="mb-3">
              <div class="text-sm font-semibold text-gray-700 mb-1">Tax(es)</div>
              <div id="active-tax-list" class="flex flex-wrap gap-2 text-xs"></div>
              <p class="text-xs text-gray-500 mt-1">
                Applied automatically. Combined rate:
                <span id="combined-tax-percent">0%</span>
              </p>
            </div>

            <div class="text-sm text-gray-700 space-y-1 mb-3">
              <div class="flex justify-between"><span>Subtotal</span> <span id="subtotal-amount">0 MMK</span></div>
              <div class="flex justify-between"><span>Tax amount</span> <span id="tax-amount">0 MMK</span></div>
            </div>

            <div class="flex justify-between font-bold text-xl border-t pt-3">
              <span>Total</span>
              <span id="total-amount" aria-live="polite">0 MMK</span>
            </div>
          </div>

          <div class="text-sm text-gray-600">Double-check your order before checkout.</div>

          <!-- Actions -->
          <div class="flex flex-wrap gap-2.5 mt-2">
            <button
              type="submit"
              class="flex-1 min-w-[200px] py-3.5 px-4 rounded-full font-bold text-base cursor-pointer bg-green-700 text-white disabled:bg-gray-300 disabled:cursor-not-allowed"
              id="confirmBtn"
              disabled
            >
              Order Confirm
            </button>

            <button
              type="button"
              class="flex-1 min-w-[200px] py-3.5 px-4 rounded-full font-bold text-base cursor-pointer bg-white text-green-700 border border-green-700"
              id="backBtn"
            >
              Back
            </button>
          </div>
        </div>
      </form>
    </main>

    <!-- Success Modal -->
    <div class="fixed inset-0 bg-black/50 flex justify-center items-center z-[1000] hidden" id="successModal">
      <div class="bg-white rounded-xl p-6 text-center max-w-xs w-[90%] relative">
        <button class="absolute top-2.5 right-4 text-xl text-gray-400 hover:text-gray-600" aria-label="Close" id="successCloseBtn">&times;</button>
        <h3 class="mb-2.5 text-xl font-semibold">Success</h3>
        <p class="text-sm text-gray-600 mb-5">Your order has been successfully placed and will be prepared soon.</p>
        <button class="bg-green-700 text-white border-none py-2.5 px-5 rounded-full font-bold cursor-pointer hover:bg-green-800 transition" id="goStatusBtn">Go to Order Status</button>
      </div>
    </div>

    <!-- Error Modal -->
    <div class="fixed inset-0 bg-black/50 flex justify-center items-center z-[1000] hidden" id="errorModal">
      <div class="bg-white rounded-xl p-6 text-center max-w-xs w-[90%] relative">
        <button class="absolute top-2.5 right-4 text-xl text-gray-400 hover:text-gray-600" aria-label="Close" id="errorCloseBtn">&times;</button>
        <h3 class="mb-2.5 text-xl font-semibold text-red-600">Error</h3>
        <p class="text-sm text-gray-600 mb-5">{{ $errors->first('error') }}</p>
        <button class="bg-red-600 text-white border-none py-2.5 px-5 rounded-full font-bold cursor-pointer hover:bg-red-700 transition" id="errorOkBtn">Close</button>
      </div>
    </div>

    <script>
      // ---- Config ----
      const OPEN = "08:00";   // store opens 08:00
      const CLOSE = "22:00";  // store closes 22:00
      const LEAD_MIN = 20;    // minimum lead time in minutes

      // ---- Utilities ----
      function toHHMM(d) {
        const hh = String(d.getHours()).padStart(2, '0');
        const mm = String(d.getMinutes()).padStart(2, '0');
        return `${hh}:${mm}`;
      }
      function sameDay(a, b) {
        return a && b && a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
      }
      function fmtMMK(n){ return (n || 0).toLocaleString() + ' MMK'; }

      // ---- Flatpickr Init ----
      const fpDate = flatpickr("#pickup-date", {
        dateFormat: "d/m/Y",
        minDate: "today",
        onChange: () => { syncMinTime(); validateForm(); },
      });

      const fpTime = flatpickr("#pickup-time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",
        time_24hr: false,
        minuteIncrement: 5,
        onOpen: syncMinTime,
        onChange: validateForm,
      });

      function syncMinTime() {
        const selected = fpDate.selectedDates[0];
        const now = new Date();
        let min = OPEN;

        if (selected && sameDay(selected, now)) {
          const lead = new Date(now.getTime() + LEAD_MIN * 60000);
          const hhmm = toHHMM(lead);
          if (hhmm > min) min = hhmm; // respect both opening time and lead time
        }
        fpTime.set("minTime", min);
        fpTime.set("maxTime", CLOSE);
      }

      // ---- Form Validation ----
      const phoneEl = document.getElementById("phone");
      const form = document.getElementById("checkoutForm");
      const confirmBtn = document.getElementById("confirmBtn");

      function validateForm() {
        const dateOk = !!document.getElementById("pickup-date").value.trim();
        const timeOk = !!document.getElementById("pickup-time").value.trim();
        const phone = phoneEl.value.trim();
        const phoneOk = /^[0-9]{7,12}$/.test(phone);
        confirmBtn.disabled = !(dateOk && timeOk && phoneOk);
      }

      phoneEl.addEventListener("input", () => {
        // Keep digits only
        const cur = phoneEl.value;
        const digits = cur.replace(/\D/g, "");
        if (cur !== digits) phoneEl.value = digits;
        validateForm();
      });

      // Prevent double submit
      form.addEventListener("submit", () => {
        confirmBtn.disabled = true;
        confirmBtn.setAttribute("aria-busy", "true");
      });

      // Back button
      document.getElementById("backBtn").addEventListener("click", () => history.back());

      // ---- Totals (ALL active taxes, read-only) ----
      function renderTaxChips(taxes) {
        const list = document.getElementById('active-tax-list');
        list.innerHTML = '';
        (taxes || []).forEach(t => {
          const chip = document.createElement('span');
          chip.className = 'inline-flex items-center gap-1 px-2 py-1 rounded-full bg-green-50 text-green-700 border border-green-200';
          chip.textContent = `${t.name} (${t.percent}%)`;
          list.appendChild(chip);
        });
      }

      function updateTotal() {
        fetch("{{ route('ajax.cart.fetch-total') }}", { headers: { "Accept": "application/json" }})
          .then(res => res.json())
          .then(data => {
            document.getElementById("subtotal-amount").textContent = fmtMMK(data?.subtotal || 0);
            document.getElementById("tax-amount").textContent      = fmtMMK(data?.tax_amount || 0);
            document.getElementById("total-amount").textContent    = fmtMMK(data?.total || 0);

            renderTaxChips(data?.taxes || []);
            document.getElementById("combined-tax-percent").textContent =
              `${(data?.combined_percent ?? 0)}%`;
          })
          .catch(() => {});
      }

      // ---- Modals ----
      const successModal = document.getElementById("successModal");
      const errorModal = document.getElementById("errorModal");

      function closeModal(modal) {
        modal.classList.remove("flex");
        modal.classList.add("hidden");
      }

      document.getElementById("successCloseBtn").addEventListener("click", () => closeModal(successModal));
      document.getElementById("goStatusBtn").addEventListener("click", () => { window.location.href = "{{route('customer.take_away.order_history')}}"; });
      document.getElementById("errorCloseBtn").addEventListener("click", () => closeModal(errorModal));
      document.getElementById("errorOkBtn").addEventListener("click", () => closeModal(errorModal));

      // ---- On Load ----
      window.addEventListener('DOMContentLoaded', () => {
        updateTotal();
        syncMinTime();
        validateForm();
      });
    </script>

    <!-- Blade-controlled modals -->
    @if(session('order_success'))
      <script>
        window.addEventListener('DOMContentLoaded', () => {
          const m = document.getElementById('successModal');
          m.classList.remove('hidden'); m.classList.add('flex');
        });
      </script>
    @endif

    @if($errors->has('error'))
      <script>
        window.addEventListener('DOMContentLoaded', () => {
          const m = document.getElementById('errorModal');
          m.classList.remove('hidden'); m.classList.add('flex');
        });
      </script>
    @endif
  </body>
</html>
