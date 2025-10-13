<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Food Order - Reset Password</title>

    <link rel="icon" href="{{asset('assets/images/logo/logo.png')}}" type="image/png" />
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">

    <!-- Font Awesome for eye icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

    <style>
        .input-group .toggle-password {
            cursor: pointer;
            user-select: none;
        }
        .is-invalid {
            border-color: #dc3545 !important;
        }
        #password-error {
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
<div class="wrapper">
    <div class="authentication-reset-password d-flex align-items-center justify-content-center min-vh-100">
        <div class="row w-100 mx-2">
            <div class="col-12 col-lg-10 mx-auto">
                <div class="card">
                    <div class="row g-0">
                        <!-- Form Side -->
                        <div class="col-lg-5 border-end">
                            <div class="card-body">
                                <div class="p-4 p-md-5">
                                    <div class="text-start mb-3">
                                        <img src="{{ asset('assets/images/logo-img.png') }}" width="180" alt="Logo">
                                    </div>
                                    <h4 class="font-weight-bold">Generate New Password</h4>
                                    <p class="text-muted">Enter your new password below.</p>

                                    <!-- Form Start -->
                                    <form method="POST" action="{{ route('kitchen.reset_password_only') }}">
                                        @csrf
                                        <div class="mb-3 mt-4">
                                            <label class="form-label">New Password</label>
                                            <div class="input-group">
                                                <input type="password" name="password" id="password"
                                                       class="form-control" placeholder="Enter new password" required>
                                                <span class="input-group-text toggle-password" onclick="toggleVisibility('password', this)">
                                                    <i class="fa fa-eye"></i>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Confirm Password</label>
                                            <div class="input-group">
                                                <input type="password" name="password_confirmation" id="confirm_password"
                                                       class="form-control" placeholder="Confirm password" required>
                                                <span class="input-group-text toggle-password" onclick="toggleVisibility('confirm_password', this)">
                                                    <i class="fa fa-eye"></i>
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Validation error message -->
                                        <div id="password-error" class="text-danger d-none">
                                            Passwords do not match or are too short (minimum 6 characters).
                                        </div>

                                        <div class="d-grid gap-2 mt-4">
                                            <button type="submit" class="btn btn-primary" id="submit-btn">Change Password</button>
                                            <a href="{{ route('kitchen.login') }}" class="btn btn-light">
                                                <i class='bx bx-arrow-back mr-1'></i>Back to Login
                                            </a>
                                        </div>
                                    </form>
                                    <!-- Form End -->
                                </div>
                            </div>
                        </div>

                        <!-- Image Side -->
                        <div class="col-lg-7 d-none d-lg-block">
                            <img src="{{ asset('assets/images/login-images/forgot-password-frent-img.jpg') }}"
                                 class="card-img login-img h-100" alt="Reset Password Image">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
    function toggleVisibility(fieldId, iconSpan) {
        const input = document.getElementById(fieldId);
        const icon = iconSpan.querySelector('i');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const errorDiv = document.getElementById('password-error');
    const submitBtn = document.getElementById('submit-btn');

    function validatePassword() {
        const pwd = password.value.trim();
        const confirmPwd = confirmPassword.value.trim();

        if (pwd.length < 6 || pwd !== confirmPwd) {
            password.classList.add('is-invalid');
            confirmPassword.classList.add('is-invalid');
            errorDiv.classList.remove('d-none');
            submitBtn.disabled = true;
        } else {
            password.classList.remove('is-invalid');
            confirmPassword.classList.remove('is-invalid');
            errorDiv.classList.add('d-none');
            submitBtn.disabled = false;
        }
    }

    password.addEventListener('input', validatePassword);
    confirmPassword.addEventListener('input', validatePassword);
</script>
</body>

</html>
