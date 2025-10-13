@extends('manager.layouts.app')
@section('content')
@include('manager.components.left_sidebar')
@include('manager.components.header')
@include('manager.components.right_sidebar')
<div class="page-wrapper">
    <div class="page-content">
            <h3>Total Orders and Customers</h3>
            <div class="card">
                <div class="card-body">
                    {{-- <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm mb-3">+ Create User</a> --}}

                    <div class="table-responsive">
                        <table id="example2" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>register_date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->role }}</td>
                                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                        <td>
                                                <a href="{{ route('manager.users.show', $user->id) }}" class="btn btn-success btn-sm">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                Are you sure you want to delete user **<span id="modalUserName"></span>**? This action cannot be undone.
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
    $(document).ready(function() {
        // DataTables initialization for #example (if still needed)
        $('#example').DataTable();

        // DataTables initialization for #example2 with buttons
        var table = $('#example2').DataTable( {
            lengthChange: false,
            buttons: [
                {
                    extend: 'copyHtml5',
                    exportOptions: {
                        columns: [ 0, 1, 2, 3 ]
                    }
                },
                {
                    extend: 'excelHtml5',
                    exportOptions: {
                        columns: [ 0, 1, 2, 3 ]
                    }
                },
                {
                    extend: 'pdfHtml5',
                    exportOptions: {
                        columns: [ 0, 1, 2, 3 ]
                    }
                },
                {
                    extend: 'print',
                    exportOptions: {
                        columns: [ 0, 1, 2, 3 ]
                    }
                }
            ]
        } );

        table.buttons().container()
            .appendTo( '#example2_wrapper .col-md-6:eq(0)' );

        var deleteModal = document.getElementById('deleteConfirmationModal');

        if (deleteModal) { // Ensure the modal element exists
            deleteModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;

                var userId = button.getAttribute('data-user-id');
                var userName = button.getAttribute('data-user-name');

                var modalUserNameSpan = deleteModal.querySelector('#modalUserName');
                var deleteUserForm = deleteModal.querySelector('#deleteUserForm');

                // Update the user name in the modal body
                if (modalUserNameSpan) {
                    modalUserNameSpan.textContent = userName;
                }
                deleteUserForm.action = `/admin/users/${userId}`;
            });
        }
    });
</script>
@endsection
