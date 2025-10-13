<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>OTP Verification</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <link rel="icon" href="{{asset('assets/images/logo/logo.png')}}" type="image/png" />
    <style>
        input.otp-input::-webkit-inner-spin-button,
        input.otp-input::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input.otp-input {
            text-align: center;
            font-size: 1.25rem;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-100 to-green-50 px-4 py-6">
<div class="w-full max-w-sm sm:max-w-md bg-white shadow-md rounded-xl p-4 sm:p-6 space-y-6">
    <!-- Logo -->
    <div class="text-center">
        <img src="{{ asset('assets/images/logo/logo.png') }}" class="w-12 h-12 sm:w-14 sm:h-14 mx-auto" alt="Logo">
        <h1 class="text-lg sm:text-xl font-bold text-green-700 mt-2">Food Order</h1>
    </div>

    <!-- Illustration -->
    <div class="w-full flex justify-center">
        <img src="{{ asset('assets/images/auth/otp.png') }}" alt="OTP" class="w-3/4 sm:w-2/3 h-32 sm:h-40 object-contain">
    </div>

    <!-- Title -->
    <div class="text-center px-1 sm:px-4">
        <h2 class="text-xl sm:text-2xl font-bold text-green-700">Enter OTP</h2>
        <p class="text-sm text-gray-600 mt-1">A 6-digit code has been sent to your email.</p>
    </div>

    <!-- OTP Form -->
    <form method="POST" action="{{ route('manager.verify_otp_only') }}" onsubmit="return fillOtp()" class="space-y-4">
        @csrf

        @if($errors->any())
            <div class="bg-red-100 text-red-800 text-sm p-2 rounded">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- OTP Input Boxes -->
        <div class="flex flex-wrap justify-center gap-2">
            @for ($i = 0; $i < 6; $i++)
                <input type="text" maxlength="1"
                       class="otp-input w-10 h-10 sm:w-12 sm:h-12 rounded-md border bg-gray-100 text-gray-900 focus:ring-2 focus:ring-green-500 outline-none"
                       required />
            @endfor
        </div>

        <!-- Hidden full OTP -->
        <input type="hidden" name="otp" id="otpFullValue" />

        <!-- Submit -->
        <button type="submit"
                class="w-full bg-green-700 hover:bg-green-800 text-white font-semibold py-2.5 sm:py-3 rounded-md transition duration-200 text-sm sm:text-base">
            Verify OTP
        </button>
    </form>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const inputs = document.querySelectorAll(".otp-input");

    inputs.forEach((input, index) => {
        input.addEventListener("input", (e) => {
            if (e.inputType !== "deleteContentBackward" && input.value !== "") {
                inputs[index + 1]?.focus();
            }
        });

        input.addEventListener("keydown", (e) => {
            if (e.key === "Backspace" && input.value === "") {
                inputs[index - 1]?.focus();
            }
        });
    });

    function fillOtp() {
        const code = [...inputs].map(i => i.value).join("");
        if (code.length !== inputs.length) {
            alert("Please enter all 6 digits of the OTP.");
            return false;
        }
        document.getElementById("otpFullValue").value = code;
        return true;
    }
</script>

<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    @if(Session::has('message'))
    var type = "{{ Session::get('alert-type','info') }}";
    switch (type) {
        case 'info': toastr.info("{{ Session::get('message') }}"); break;
        case 'success': toastr.success("{{ Session::get('message') }}"); break;
        case 'warning': toastr.warning("{{ Session::get('message') }}"); break;
        case 'error': toastr.error("{{ Session::get('message') }}"); break;
    }
    @endif
</script>
</body>
</html>
