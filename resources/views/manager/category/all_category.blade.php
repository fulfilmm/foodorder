@extends('manager.layouts.app')
@section('content')
@include('manager.components.left_sidebar')
@include('manager.components.header')
@include('manager.components.right_sidebar')

<div class="page-wrapper">
    <div class="page-content">
        <h3 >Category Management</h3>
        <h3 class="mt-5">All Category</h3>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablesTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Created Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->created_at->format('Y-m-d H:i') }}</td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong><span id="modalUserName"></span></strong>? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteUserForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom-js')




<script>
    $(document).ready(function () {
        function initDataTable(id) {
            var table = $(`#${id}`).DataTable({
                lengthChange: false,
                // buttons: ['copyHtml5', 'excelHtml5', 'pdfHtml5', 'print'],
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
            ],
                ordering: true
            });
            table.buttons().container().appendTo(`#${id}_wrapper .col-md-6:eq(0)`);
        }

        initDataTable('tablesTable');

        // Delete modal logic
        const deleteModal = document.getElementById('deleteConfirmationModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const userName = button.getAttribute('data-user-name');

            deleteModal.querySelector('#modalUserName').textContent = userName;
            deleteModal.querySelector('#deleteUserForm').action = `/admin/categories/${userId}`;
        });
    });
</script>
@endsection
