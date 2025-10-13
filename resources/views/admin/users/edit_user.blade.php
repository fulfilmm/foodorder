

@extends('admin.layouts.app')
@section('content')
@include('admin.components.left_sidebar')
@include('admin.components.header')
@include('admin.components.right_sidebar')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">User Profile Edit</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><i class="bx bx-home-alt"></i>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">User Update</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->
        <div class="container">
            <div class="main-body">
                <div class="row">
                    <div class="col-lg-12" >
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-column align-items-center text-center">
                                    <div class="mt-3">
                                        <h4>{{$user->name}}</h4>
                                        <p class="text-secondary mb-1">{{$user->role}} role</p>
                                        <p class="text-muted font-size-sm">{{$user->email}}</p>


                                        {{-- Delete Button: Triggers the confirmation modal --}}
                                        <button type="button" class="btn btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteConfirmationModal"
                                            data-user-id="{{ $user->id }}"
                                            data-user-name="{{ $user->name }}">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                                <hr class="my-4" />
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                        <h6 class="mb-0">Name</h6>
                                        <span class="text-secondary">{{$user->name}}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                        <h6 class="mb-0">Role</h6>
                                        <span class="text-secondary">{{$user->role}}</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                        <h6 class="mb-0">Email</h6>
                                        <span class="text-secondary">{{$user->email}}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">

                                {{-- Form to edit user details --}}
                                <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Name</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" />
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Email</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" />
                                        </div>
                                    </div>
                                    {{-- Add role selection here if you want to allow editing the role --}}
                                    <div class="row mb-3">
                                        <div class="col-sm-3">
                                            <h6 class="mb-0">Role</h6>
                                        </div>
                                        <div class="col-sm-9 text-secondary">
                                            <select class="form-select" name="role">
                                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                                <option value="manager" {{ old('role', $user->role) == 'manager' ? 'selected' : '' }}>Manager</option>
                                                <option value="kitchen" {{ old('role', $user->role) == 'kitchen' ? 'selected' : '' }}>Kitchen</option>
                                                <option value="waiter" {{ old('role', $user->role) == 'waiter' ? 'selected' : '' }}>Waiter</option>
                                                <option value="customer" {{ old('role', $user->role) == 'customer' ? 'selected' : '' }}>Customer</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-3"></div>
                                        <div class="col-sm-9 text-secondary">
                                            <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                                            {{-- Add a Cancel button to hide the form again --}}
                                            <button type="button" class="btn btn-secondary px-4" onclick="editUser()">Cancel</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the user **<span id="modalUserName"></span>**?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteUserForm" method="POST" action=""> {{-- Action will be set by JS --}}
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-js')
<script>

    // JavaScript for handling the delete confirmation modal
    document.addEventListener('DOMContentLoaded', function () {
        var deleteModal = document.getElementById('deleteConfirmationModal');

        // Check if the modal element exists before adding the event listener
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function (event) {
                // Button that triggered the modal
                var button = event.relatedTarget;

                // Extract info from data-* attributes
                var userId = button.getAttribute('data-user-id');
                var userName = button.getAttribute('data-user-name');

                // Get references to elements inside the modal
                var modalUserNameSpan = deleteModal.querySelector('#modalUserName');
                var deleteUserForm = deleteModal.querySelector('#deleteUserForm');

                // Update the user name in the modal body
                if (modalUserNameSpan) {
                    modalUserNameSpan.textContent = userName;
                }

                // Set the action of the delete form
                // Ensure this matches your Laravel delete route structure (e.g., /admin/users/{id})
                deleteUserForm.action = `/admin/users/${userId}`;
            });
        }
    });
</script>
@endsection
<!--end page wrapper -->
