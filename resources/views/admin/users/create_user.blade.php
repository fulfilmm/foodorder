@extends('admin.layouts.app')

@section('content')
@include('admin.components.left_sidebar')
@include('admin.components.header')
@include('admin.components.right_sidebar')

<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">User Management</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.home') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.admin') }}">Users</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create New User</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="container">
            <div class="main-body">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-4">Create New User</h5>

                        {{-- Display Validation Errors --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.users.store') }}" method="POST">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <label for="name" class="form-label">Name</label>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus />
                                    @error('name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <label for="email" class="form-label">Email</label>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required />
                                    @error('email')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Password Input with Show/Hide Toggle --}}
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <label for="password" class="form-label">Password</label>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <div class="input-group"> {{-- Use Bootstrap input-group for consistent styling --}}
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required autocomplete="new-password" />
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fa-solid fa-eye"></i> {{-- Eye icon --}}
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Confirm Password Input with Show/Hide Toggle --}}
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" />
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                            <i class="fa-solid fa-eye"></i> {{-- Eye icon --}}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <label for="role" class="form-label">Role</label>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                        <option value="">Select a Role</option>
                                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                                        <option value="kitchen" {{ old('role') == 'kitchen' ? 'selected' : '' }}>Kitchen</option>
                                        <option value="waiter" {{ old('role') == 'waiter' ? 'selected' : '' }}>Waiter</option>
                                        <option value="customer" {{ old('role') == 'customer' ? 'selected' : '' }}>Customer</option>
                                    </select>
                                    @error('role')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <button type="submit" class="btn btn-primary px-4">Create User</button>
                                    <a href="{{ route('admin.users.admin') }}" class="btn btn-secondary px-4">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-js')
<script>
    // Function to toggle password visibility
    function togglePasswordVisibility(passwordFieldId, toggleButtonId) {
        const passwordField = document.getElementById(passwordFieldId);
        const toggleButton = document.getElementById(toggleButtonId);
        const icon = toggleButton.querySelector('i');

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash'); // Change to crossed-out eye
        } else {
            passwordField.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye'); // Change back to eye
        }
    }

    // Attach event listeners when the DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        const togglePasswordBtn = document.getElementById('togglePassword');
        if (togglePasswordBtn) {
            togglePasswordBtn.addEventListener('click', function() {
                togglePasswordVisibility('password', 'togglePassword');
            });
        }

        const toggleConfirmPasswordBtn = document.getElementById('toggleConfirmPassword');
        if (toggleConfirmPasswordBtn) {
            toggleConfirmPasswordBtn.addEventListener('click', function() {
                togglePasswordVisibility('password_confirmation', 'toggleConfirmPassword');
            });
        }
    });
</script>
@endsection
