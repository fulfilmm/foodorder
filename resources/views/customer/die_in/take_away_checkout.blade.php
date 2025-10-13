<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Checkout</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap"
      rel="stylesheet"
    />
    <link
      href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"
      rel="stylesheet"
    />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
      /* Keep Flatpickr's default styles if you don't want to customize them with Tailwind */
      .flatpickr-calendar {
        font-family: "Inter", sans-serif;
      }
    </style>
  </head>
  <body class="font-sans bg-white text-gray-800 min-h-screen">
    <nav class="fixed inset-x-0 top-0 z-50 bg-white border-b border-green-200">
      <div
        class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6"
      >
        <div class="flex items-center space-x-3">
          <div class="h-9 w-9 rounded-full bg-black"></div>
          <span class="text-xl font-extrabold text-green-700">Hello!</span>
        </div>

        <div class="flex items-center space-x-6">
          <a href="#" class="flex items-center hover:text-green-600 transition">
            <svg class="h-6 w-6 fill-current" viewBox="0 0 24 24">
              <path
                d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z"
              />
            </svg>
            <span class="hidden sm:inline ml-2">Home</span>
          </a>

          <a href="#" class="flex items-center hover:text-green-600 transition">
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
            <span class="hidden sm:inline ml-2">Docs</span>
          </a>

          <a href="#" class="flex items-center hover:text-green-600 transition">
            <svg
              class="h-6 w-6 stroke-current"
              fill="none"
              stroke-width="2"
              viewBox="0 0 24 24"
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

    <main class="pt-20 p-4 mx-auto max-w-3xl">
      <h2 class="text-green-700 mb-4 text-2xl font-bold md:text-3xl">
        Checkout
      </h2>

      <div class="flex flex-col gap-5">
        <div class="flex flex-col gap-2">
          <label for="pickup-date" class="font-bold text-base"
            >Pickup Date</label
          >
          <div class="flex items-center bg-gray-100 rounded-lg p-3 gap-2.5">
            <i class="text-lg text-green-700">📅</i>
            <input
              type="text"
              id="pickup-date"
              placeholder="Select date"
              class="border-none bg-transparent text-base flex-1 outline-none shadow-none focus:ring-0"
            />
          </div>
        </div>

        <div class="flex flex-col gap-2">
          <label for="pickup-time" class="font-bold text-base"
            >Pickup Time</label
          >
          <div class="flex items-center bg-gray-100 rounded-lg p-3 gap-2.5">
            <i class="text-lg text-green-700">⏰</i>
            <input
              type="text"
              id="pickup-time"
              placeholder="Select time"
              class="border-none bg-transparent text-base flex-1 outline-none shadow-none focus:ring-0"
            />
          </div>
        </div>

        <div class="flex flex-col gap-2">
          <label for="phone" class="font-bold text-base">Mobile Number</label>
          <div class="flex items-center bg-gray-100 rounded-lg p-3 gap-2.5">
            <span class="text-gray-700">🇲🇲 +95</span>
            <input
              type="tel"
              id="phone"
              placeholder="Enter phone number"
              class="border-none bg-transparent text-base flex-1 outline-none shadow-none focus:ring-0"
            />
          </div>
        </div>

        <div class="bg-green-50 p-4 rounded-lg text-green-700 font-bold">
          <div>✔ Payment Method</div>
          <p class="text-gray-700 font-normal mt-1.5">
            Kindly note that payment is required in cash upon arrival.
          </p>
        </div>

        <div
          class="text-lg flex justify-between font-bold border-t border-gray-200 pt-4"
        >
          <span>Total</span>
          <span id="total-amount">21,000 MMK</span>
        </div>

        <div class="text-sm text-gray-600">
          Double check your order before Checkout.
        </div>

        <div class="flex flex-wrap gap-2.5 mt-5">
          <button
            class="flex-1 min-w-[200px] py-3.5 px-4 rounded-full font-bold text-base cursor-pointer bg-green-700 text-white disabled:bg-gray-300 disabled:cursor-not-allowed"
            id="confirmBtn"
            onclick="confirmOrder()"
            disabled
          >
            Order Confirm
          </button>
          <button
            class="flex-1 min-w-[200px] py-3.5 px-4 rounded-full font-bold text-base cursor-pointer bg-white text-green-700 border border-green-700"
            onclick="goBack()"
          >
            Back
          </button>
        </div>
      </div>
    </main>

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

    <script>
      flatpickr("#pickup-date", {
        dateFormat: "d/m/Y",
        minDate: "today",
        onChange: validateForm,
      });
      flatpickr("#pickup-time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",
        time_24hr: false,
        minTime: "08:00",
        maxTime: "22:00",
        onChange: validateForm,
      });
      document.getElementById("phone").addEventListener("input", validateForm);

      function validateForm() {
        const date = document.getElementById("pickup-date").value;
        const time = document.getElementById("pickup-time").value;
        const phone = document.getElementById("phone").value.trim();
        const phoneRegex = /^[0-9]{7,12}$/;
        const isValid = date && time && phoneRegex.test(phone);
        document.getElementById("confirmBtn").disabled = !isValid;
      }

      function confirmOrder() {
        const modal = document.getElementById("successModal");
        modal.classList.remove("hidden"); // Use Tailwind's hidden class
        modal.classList.add("flex"); // Use Tailwind's flex class
      }

      function closeModal() {
        const modal = document.getElementById("successModal");
        modal.classList.remove("flex"); // Use Tailwind's flex class
        modal.classList.add("hidden"); // Use Tailwind's hidden class
      }

      function goToStatus() {
        window.location.href = "/order_history.html";
      }

      function goBack() {
        window.history.back();
      }
    </script>
  </body>
</html>
