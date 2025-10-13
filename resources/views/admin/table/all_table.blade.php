@extends('admin.layouts.app')
@section('content')
@include('admin.components.left_sidebar')
@include('admin.components.header')
@include('admin.components.right_sidebar')

<div class="page-wrapper">
    <div class="page-content">
        <h3 >Tables</h3>
        <div class="card">
            <div class="card-body">
                <a href="{{ route('admin.tables.create') }}" class="btn btn-primary btn-sm mb-3">+ Create Table</a>

                <div class="table-responsive">
                    <table id="tablesTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tables as $table)
                                <tr>
                                    <td>{{ $table->name }}</td>
                                    <td>{{ $table->code }}</td>
<td>
    @php
        $avail = $table->availability_status; // 'available' or 'unavailable'
        $isAvailable = $avail === 'available';
        $badge = $isAvailable ? 'bg-success' : 'bg-danger';
    @endphp

    <span class="badge {{ $badge }}">{{ ucfirst($avail) }}</span>

    {{-- Optional: explain why --}}
    @if($table->latestOrderToday)
        <small class="text-muted d-block">
            Latest order today: {{ strtoupper($table->latestOrderToday->status) }}
            • {{ $table->latestOrderToday->created_at->format('H:i') }}
        </small>
    @else
        <small class="text-muted d-block">No orders today</small>
    @endif
</td>
                                    <td>{{ $table->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="#" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#qrModal-{{ $table->id }}">View QR</a>
                                        <a href="{{ route('admin.tables.show', $table->id) }}" class="btn btn-success btn-sm">View</a>
                                        <a  href="{{ route('admin.tables.edit', $table->id) }}" class="btn btn-primary btn-sm">Edit</a>

                                        <button type="button" class="btn btn-danger btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteConfirmationModal"
                                            data-user-id="{{ $table->id }}"
                                            data-user-name="{{ $table->name }}">
                                            Delete
                                        </button>
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

<!-- QR Modals -->
@foreach ($tables as $table)
<div class="modal fade" id="qrModal-{{ $table->id }}" tabindex="-1" aria-labelledby="qrModalLabel-{{ $table->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrModalLabel-{{ $table->id }}">QR Code for {{ $table->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset($table->qr_path) }}" alt="QR Code" style="max-width: 100%; height: auto;">
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="printImage('{{ asset($table->qr_path) }}')">Print</button>
                <a class="btn btn-success" href="{{ asset($table->qr_path) }}" download>Save & Download</a>
            </div>
        </div>
    </div>
</div>
@endforeach
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

        initDataTable('usersTable');
        initDataTable('tablesTable');

        // Delete modal logic
        const deleteModal = document.getElementById('deleteConfirmationModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const userName = button.getAttribute('data-user-name');

            deleteModal.querySelector('#modalUserName').textContent = userName;
            deleteModal.querySelector('#deleteUserForm').action = `/admin/tables/${userId}`;
        });
    });

    function printImage(src) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html><head><title>Print QR</title>
            <style>body{text-align:center;padding:20px;}img{max-width:100%;}</style>
            </head><body>
            <img src="${src}" onload="window.print();window.onafterprint=function(){window.close();}">
            </body></html>
        `);
        printWindow.document.close();
    }
</script>
@endsection
