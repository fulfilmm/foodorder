{{-- <!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('assets/images/logo/logo.png') }}" type="image/png" />
    <title>Dining Option</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://kit.fontawesome.com/34ce5b6af8.js" crossorigin="anonymous"></script>

    <style>
      html,
      body {
        height: 100%;
        overflow: hidden;
      }
      .no-scrollbar::-webkit-scrollbar {
        display: none;
      }
      .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
      }
    </style>
  </head>
  <body class="font-sans bg-white text-gray-800 flex flex-col h-screen">
   @php
      $initialDistinct = count(session('cart', [])); // distinct product_ids
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

    <div class="flex-1 overflow-hidden pt-14 flex flex-col w-full">
      <div class="text-2xl font-bold px-4 sm:px-6 bg-white z-20 sticky top-5 w-full border-b border-gray-200 text-green-700">
        Cart
      </div>

      @php
      $cart = session('cart', []);
      $total = 0;
      @endphp

      <div id="cart-items" class="flex-1 overflow-y-auto no-scrollbar px-4 sm:px-6 pb-4 pt-10">
        <!-- Cart will be loaded here via JavaScript -->
      </div>

       <div class="px-4 sm:px-6 py-4">
        <div class="border border-gray-100 rounded-xl p-4 mt-4 mb-8 bg-white shadow-sm">

          <!-- Active taxes -->
          <div class="mb-3">
            <div class="text-sm font-semibold text-gray-700 mb-1">Active tax(es)</div>
            <div id="active-tax-list" class="flex flex-wrap gap-2 text-xs"></div>
            <p class="text-xs text-gray-500 mt-1">
              Applied automatically. Combined rate:
              <span id="combined-tax-percent">0%</span>
            </p>
          </div>

          <!-- Totals -->
          <div class="text-sm text-gray-700 space-y-1 mb-3">
            <div class="flex justify-between"><span>Subtotal</span> <span id="subtotal-amount">0 MMK</span></div>
            <div class="flex justify-between"><span>Tax amount</span> <span id="tax-amount">0 MMK</span></div>
          </div>

<div class="flex justify-between font-bold text-xl mb-4 border-t pt-3">
            <span>Total</span>
            <span id="total-price">{{ number_format($total) }} MMK</span>
          </div>

          <div class="flex gap-2.5">
            <button
              class="flex-1 py-3 px-4 rounded-full font-bold border-none cursor-pointer bg-green-700 text-white hover:bg-green-800 transition-colors duration-200"
              onclick="autoCheckout()"
            >
              Checkout
            </button>
            <button
              class="flex-1 py-3 px-4 rounded-full font-bold border border-green-700 bg-white text-green-700 hover:bg-green-50 transition-colors duration-200"
              onclick="window.location.href='{{ route('customer.die_in.home') }}'"
            >
              Back
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <div
      class="fixed inset-0 bg-black/50 flex justify-center items-center z-[1000] hidden"
      id="successModal"
    >
      <div
        class="bg-white rounded-xl p-6 text-center max-w-xs w-[90%] relative"
      >
        <div
          class="absolute top-2.5 right-4 text-xl cursor-pointer text-gray-400 hover:text-gray-600"
          onclick="closeModal()"
        >
          &times;
        </div>
        <h3 class="mb-2.5 text-xl font-semibold">Success</h3>
        <p class="text-sm text-gray-600 mb-5">
          Your order has been successfully placed and will be prepared soon.
        </p>
        <button
          class="bg-green-700 text-white border-none py-2.5 px-5 rounded-full font-bold cursor-pointer hover:bg-green-800 transition"
          onclick="goToStatus()"
        >
          Go to Order Status
        </button>
      </div>
    </div>
    <!-- Error Modal -->
    <div
    class="fixed inset-0 bg-black/50 flex justify-center items-center z-[1000] hidden"
    id="errorModal"
  >
    <div class="bg-white rounded-xl p-6 text-center max-w-xs w-[90%] relative">
      <div
        class="absolute top-2.5 right-4 text-xl cursor-pointer text-gray-400 hover:text-gray-600"
        onclick="closeErrorModal()"
      >
        &times;
      </div>
      <h3 class="mb-2.5 text-xl font-semibold text-red-600">Error</h3>
      <p class="text-sm text-gray-600 mb-5">
        {{ $errors->first('error') }}
      </p>
      <button
        class="bg-red-600 text-white border-none py-2.5 px-5 rounded-full font-bold cursor-pointer hover:bg-red-700 transition"
        onclick="closeErrorModal()"
      >
        Close
      </button>
    </div>
  </div>

    <script>
        function fmtMMK(n){ return (n || 0).toLocaleString() + ' MMK'; }

      function openModal(modalId, errorMsg = '') {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        if (modalId === 'errorModal' && errorMsg) {
          document.getElementById('errorMessage').textContent = errorMsg;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }

      function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }

      function goToStatus() {
        window.location.href = "{{route('customer.die_in.order_history')}}";
      }

      function goBack() {
        window.history.back();
      }

      function autoCheckout() {
        const now = new Date();

        // Format date as dd/mm/yyyy
        const date = now.toLocaleDateString("en-GB");
        const time = now.toTimeString().slice(0, 5); // "HH:MM"
        const phone = Math.floor(100000000 + Math.random() * 900000000); // 9-digit fake phone

        fetch("{{route('customer.die_in.checkout.store')}}", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
          },
          body: JSON.stringify({
            pickup_date: date,
            pickup_time: time,
            phone: phone,
            order_type: "dine_in",
          }),
        })
          .then(async (res) => {
            const text = await res.text();

            if (!res.ok) {
              // If server responds with error, show message in error modal
              let json;
              try {
                json = JSON.parse(text);
              } catch {
                json = null;
              }
              const errorMsg = json && json.message ? json.message : text;
              throw new Error(errorMsg);
            }

            // Parse valid JSON response
            return JSON.parse(text);
          })
          .then((data) => {
            if (data.success) {
              loadCart();

              // Show success modal
              openModal('successModal');
            } else {
              // Show error modal with message
              openModal('errorModal', data.message || 'Order failed.');
            }
          })
          .catch((err) => {
            console.error("Error during checkout:", err);
            openModal('errorModal', err.message || "Something went wrong.");
          });
      }

      function loadCart() {
        fetch("{{ route('ajax.cart.fetch') }}")
          .then((res) => res.text())
          .then((html) => {
            document.getElementById("cart-items").innerHTML = html;
            // updateTotal();
            refreshTotals();
            updateNavCount();

          });
      }

      function updateQuantity(productId, change) {
        fetch("{{ route('cart.update.ajax') }}", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
          },
          body: JSON.stringify({ product_id: productId, change: change }),
        }).then(() => loadCart());
      }

      function removeItem(productId) {
        fetch("{{ route('cart.remove.ajax') }}", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
          },
          body: JSON.stringify({ product_id: productId }),
        }).then(() => loadCart());
      }

      function updateTotal() {
        fetch("{{ route('ajax.cart.fetch-total') }}")
          .then((res) => res.json())
          .then((data) => {
            document.getElementById("total-price").textContent = data.total;
          });
      }
      function updateNavCount() {
        fetch("{{ route('ajax.cart.counts') }}")
          .then(res => res.json())
          .then(({ distinct }) => {
            const badge = document.getElementById('nav-cart-count');
            if (badge) badge.textContent = distinct;
          })
          .catch(() => {});
      }

      // Fetch totals; applies ALL active taxes automatically
      function refreshTotals(){
        fetch(`{{ route('ajax.cart.fetch-total') }}`)
          .then(res => res.json())
          .then(d => {
            document.getElementById('subtotal-amount').textContent = fmtMMK(d.subtotal);
            document.getElementById('tax-amount').textContent      = fmtMMK(d.tax_amount);
            document.getElementById('total-price').textContent     = fmtMMK(d.total);

            // Render tax chips
            const list = document.getElementById('active-tax-list');
            list.innerHTML = '';
            (d.taxes || []).forEach(t => {
              const chip = document.createElement('span');
              chip.className = 'inline-flex items-center gap-1 px-2 py-1 rounded-full bg-green-50 text-green-700 border border-green-200';
              chip.textContent = `${t.name} (${t.percent}%)`;
              list.appendChild(chip);
            });
            document.getElementById('combined-tax-percent').textContent = `${d.combined_percent}%`;
          });
      }
  function saveComment(productId, commentText) {
    fetch("{{ route('cart.comment.ajax') }}", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": "{{ csrf_token() }}"
      },
      body: JSON.stringify({ product_id: productId, comment: commentText })
    })
    .then(res => res.json())
    .then(data => {
      // Optional: toast or tiny checkmark
      // console.log('Saved comment', data);
    })
    .catch(err => console.error('Failed to save comment', err));
  }
  function showNoteEditor(pid) {
  document.getElementById('note-edit-' + pid).classList.remove('hidden');
  const v = document.getElementById('note-view-' + pid);
  const e = document.getElementById('note-empty-' + pid);
  if (v) v.classList.add('hidden');
  if (e) e.classList.add('hidden');
  document.getElementById('note-input-' + pid).focus();
}

function cancelEditNote(pid) {
  loadCart(); // reload UI to original state
}

function saveNote(pid) {
  const btn = document.getElementById('save-note-btn-' + pid);
  const hint = document.getElementById('note-hint-' + pid);
  const text = document.getElementById('note-input-' + pid).value.trim();

  btn.disabled = true;
  hint.textContent = 'Saving…';

  fetch("{{ route('cart.comment.ajax') }}", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": "{{ csrf_token() }}"
    },
    body: JSON.stringify({ product_id: pid, comment: text })
  })
  .then(res => res.json())
  .then(data => {
    if (!data.ok) throw new Error();
    hint.textContent = 'Saved!';
    setTimeout(() => loadCart(), 200);
  })
  .catch(() => hint.textContent = 'Error saving note.')
  .finally(() => btn.disabled = false);
}

      document.addEventListener("DOMContentLoaded", loadCart);
    </script>
  </body>
</html>

 --}}


 <!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="{{ asset('assets/images/logo/logo.png') }}" type="image/png" />
    <title>Dining Option</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://kit.fontawesome.com/34ce5b6af8.js" crossorigin="anonymous"></script>

    <style>
      html, body { height: 100%; overflow: hidden; }
      .no-scrollbar::-webkit-scrollbar { display: none; }
      .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
  </head>
  <body class="font-sans bg-white text-gray-800 flex flex-col h-screen">
    @php $initialDistinct = count(session('cart', [])); @endphp

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

    <div class="flex-1 overflow-hidden pt-14 flex flex-col w-full">
      <div class="text-2xl font-bold px-4 sm:px-6 bg-white z-20 sticky top-5 w-full border-b border-gray-200 text-green-700">
        Cart
      </div>

      <div id="cart-items" class="flex-1 overflow-y-auto no-scrollbar px-4 sm:px-6 pb-4 pt-10">
        <!-- Cart HTML will be injected here -->
      </div>

      <div class="px-4 sm:px-6 py-4">
        <div class="border border-gray-100 rounded-xl p-4 mt-4 mb-8 bg-white shadow-sm">

          <!-- Active taxes -->
          <div class="mb-3">
            <div class="text-sm font-semibold text-gray-700 mb-1">Active tax(es)</div>
            <div id="active-tax-list" class="flex flex-wrap gap-2 text-xs"></div>
            <p class="text-xs text-gray-500 mt-1">
              Applied automatically. Combined rate:
              <span id="combined-tax-percent">0%</span>
            </p>
          </div>

          <!-- Totals -->
          <div class="text-sm text-gray-700 space-y-1 mb-3">
            <div class="flex justify-between"><span>Subtotal</span> <span id="subtotal-amount">0 MMK</span></div>
            <div class="flex justify-between"><span>Tax amount</span> <span id="tax-amount">0 MMK</span></div>
          </div>

          <div class="flex justify-between font-bold text-xl mb-4 border-t pt-3">
            <span>Total</span>
            <span id="total-price">0 MMK</span>
          </div>

          <div class="flex gap-2.5">
            <button
              class="flex-1 py-3 px-4 rounded-full font-bold border-none cursor-pointer bg-green-700 text-white hover:bg-green-800 transition-colors duration-200"
              onclick="autoCheckout()">
              Checkout
            </button>
            <button
              class="flex-1 py-3 px-4 rounded-full font-bold border border-green-700 bg-white text-green-700 hover:bg-green-50 transition-colors duration-200"
              onclick="window.location.href='{{ route('customer.die_in.home') }}'">
              Back
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black/50 flex justify-center items-center z-[1000] hidden">
      <div class="bg-white rounded-xl p-6 text-center max-w-xs w-[90%] relative">
        <button type="button" class="absolute top-2.5 right-4 text-xl cursor-pointer text-gray-400 hover:text-gray-600"
                onclick="closeModal('successModal')">&times;</button>
        <h3 class="mb-2.5 text-xl font-semibold">Success</h3>
        <p class="text-sm text-gray-600 mb-5">
          Your order has been successfully placed and will be prepared soon.
        </p>
        <button class="bg-green-700 text-white border-none py-2.5 px-5 rounded-full font-bold cursor-pointer hover:bg-green-800 transition"
                onclick="goToStatus()">Go to Order Status</button>
      </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="fixed inset-0 bg-black/50 flex justify-center items-center z-[1000] hidden">
      <div class="bg-white rounded-xl p-6 text-center max-w-xs w-[90%] relative">
        <button type="button" class="absolute top-2.5 right-4 text-xl cursor-pointer text-gray-400 hover:text-gray-600"
                onclick="closeModal('errorModal')">&times;</button>
        <h3 class="mb-2.5 text-xl font-semibold text-red-600">Error</h3>
        <p id="errorMessage" class="text-sm text-gray-600 mb-5"></p>
        <button class="bg-red-600 text-white border-none py-2.5 px-5 rounded-full font-bold cursor-pointer hover:bg-red-700 transition"
                onclick="closeModal('errorModal')">Close</button>
      </div>
    </div>

    <script>
      function fmtMMK(n){ return (Number(n)||0).toLocaleString() + ' MMK'; }

      function openModal(modalId, messageHtml = '') {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        if (modalId === 'errorModal' && messageHtml) {
          const el = document.getElementById('errorMessage');
          if (el) el.innerHTML = messageHtml; // allow bullet list via <br>
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      }
      function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
      }

      function goToStatus() { window.location.href = "{{ route('customer.die_in.order_history') }}"; }

      function loadCart() {
        fetch("{{ route('ajax.cart.fetch') }}")
          .then((res) => res.text())
          .then((html) => {
            document.getElementById("cart-items").innerHTML = html;
            refreshTotals();
            updateNavCount();
          });
      }

      function updateQuantity(productId, change) {
        fetch("{{ route('cart.update.ajax') }}", {
          method: "POST",
          headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
          body: JSON.stringify({ product_id: productId, change: change }),
        }).then(loadCart);
      }

      function removeItem(productId) {
        fetch("{{ route('cart.remove.ajax') }}", {
          method: "POST",
          headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
          body: JSON.stringify({ product_id: productId }),
        }).then(loadCart);
      }

      function updateNavCount() {
        fetch("{{ route('ajax.cart.counts') }}")
          .then(res => res.json())
          .then(({ distinct }) => {
            const badge = document.getElementById('nav-cart-count');
            if (badge) badge.textContent = distinct;
          })
          .catch(() => {});
      }

      // Totals with active taxes from backend
      function refreshTotals(){
        fetch(`{{ route('ajax.cart.fetch-total') }}`)
          .then(res => res.json())
          .then(d => {
            document.getElementById('subtotal-amount').textContent = fmtMMK(d.subtotal);
            document.getElementById('tax-amount').textContent      = fmtMMK(d.tax_amount);
            document.getElementById('total-price').textContent     = fmtMMK(d.total);

            const list = document.getElementById('active-tax-list');
            list.innerHTML = '';
            (d.taxes || []).forEach(t => {
              const chip = document.createElement('span');
              chip.className = 'inline-flex items-center gap-1 px-2 py-1 rounded-full bg-green-50 text-green-700 border border-green-200';
              chip.textContent = `${t.name} (${t.percent}%)`;
              list.appendChild(chip);
            });
            document.getElementById('combined-tax-percent').textContent = `${d.combined_percent}%`;
          });
      }

      async function autoCheckout() {
        const now  = new Date();
        const date = now.toLocaleDateString("en-GB");    // dd/mm/yyyy
        const time = now.toTimeString().slice(0, 5);     // HH:MM
        const phone = Math.floor(100000000 + Math.random() * 900000000);

        // 1) Preflight: validate stock
        try {
          const vRes  = await fetch("{{ route('ajax.cart.validate') }}");
          const vText = await vRes.text();
          let vData; try { vData = JSON.parse(vText); } catch { vData = {}; }

          if (!vRes.ok || vData.ok === false) {
            const items = (vData.errors || []).map(e =>
              `• item ${e.name} — requested <b>${e.requested}</b>, only  remain <b>${e.remain}</b>`
            ).join('<br>');
            const msg = items || (vData.message || 'Insufficient stock.');
            openModal('errorModal', msg);
            return;
          }
        } catch (err) {
          openModal('errorModal', 'Could not validate stock. Please try again.');
          return;
        }

        // 2) Proceed with checkout (server re-checks within a transaction)
        try {
          const res  = await fetch("{{ route('customer.die_in.checkout.store') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ pickup_date: date, pickup_time: time, phone, order_type: "dine_in" }),
          });

          const text = await res.text();
          let data; try { data = JSON.parse(text); } catch { data = {}; }

          if (!res.ok || data.success === false) {
            const items = (data.errors || []).map(e =>
              `• ${e.name} — requested <b>${e.requested}</b>, remain <b>${e.remain}</b>`
            ).join('<br>');
            const msg = items || (data.message || 'Order failed.');
            openModal('errorModal', msg);
            return;
          }

          // success
          loadCart();
          refreshTotals();
          updateNavCount();
          openModal('successModal');

        } catch (err) {
          openModal('errorModal', err.message || "Something went wrong.");
        }
      }

      function saveComment(productId, commentText) {
    fetch("{{ route('cart.comment.ajax') }}", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": "{{ csrf_token() }}"
      },
      body: JSON.stringify({ product_id: productId, comment: commentText })
    })
    .then(res => res.json())
    .then(data => {
      // Optional: toast or tiny checkmark
      // console.log('Saved comment', data);
    })
    .catch(err => console.error('Failed to save comment', err));
  }
  function showNoteEditor(pid) {
  document.getElementById('note-edit-' + pid).classList.remove('hidden');
  const v = document.getElementById('note-view-' + pid);
  const e = document.getElementById('note-empty-' + pid);
  if (v) v.classList.add('hidden');
  if (e) e.classList.add('hidden');
  document.getElementById('note-input-' + pid).focus();
}

function cancelEditNote(pid) {
  loadCart(); // reload UI to original state
}

function saveNote(pid) {
  const btn = document.getElementById('save-note-btn-' + pid);
  const hint = document.getElementById('note-hint-' + pid);
  const text = document.getElementById('note-input-' + pid).value.trim();

  btn.disabled = true;
  hint.textContent = 'Saving…';

  fetch("{{ route('cart.comment.ajax') }}", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": "{{ csrf_token() }}"
    },
    body: JSON.stringify({ product_id: pid, comment: text })
  })
  .then(res => res.json())
  .then(data => {
    if (!data.ok) throw new Error();
    hint.textContent = 'Saved!';
    setTimeout(() => loadCart(), 200);
  })
  .catch(() => hint.textContent = 'Error saving note.')
  .finally(() => btn.disabled = false);
}

      document.addEventListener("DOMContentLoaded", loadCart);
    </script>
  </body>
</html>
