@extends('admin.layouts.app')

@section('content')
@include('admin.components.left_sidebar')
@include('admin.components.header')
@include('admin.components.right_sidebar')

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="page-content">

        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-flex align-items-center mb-4">
            <div class="breadcrumb-title pe-3">Table Management</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0 bg-transparent">
                        <li class="breadcrumb-item"><i class="bx bx-home-alt"></i></li>
                        <li class="breadcrumb-item active">Table Detail</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card shadow-sm radius-10">
            <div class="card-body p-4">

                <!-- Header Section -->
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-uppercase">{{ $table->name }}</h2>
                    {{-- <p class="text-muted">{{ $table->code }} | Status: <span class="badge bg-info">{{ $table->status }}</span>
                    </p> --}}

    @php
        $avail = $table->availability_status; // 'available' or 'unavailable'
        $isAvailable = $avail === 'available';
        $badge = $isAvailable ? 'bg-success' : 'bg-danger';
    @endphp

    <p class="text-muted">{{ $table->code }} | <span class="badge {{ $badge }}">{{ ucfirst($avail) }}</span></p>

    {{-- Optional: explain why --}}
    @if($table->latestOrderToday)
        <small class="text-muted d-block">
            Latest order today: {{ strtoupper($table->latestOrderToday->status) }}
            • {{ $table->latestOrderToday->created_at->format('H:i') }}
        </small>
    @else
        <small class="text-muted d-block">No orders today</small>
    @endif



                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <a class="btn btn-outline-primary" href="{{ route('admin.tables.edit', $table->id) }}">
    <i class="bx bx-edit-alt"></i> Edit
</a>
                        <button class="btn btn-outline-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteConfirmationModal"
                            data-user-id="{{ $table->id }}"
                            data-user-name="{{ $table->name }}">
                            <i class="bx bx-trash"></i> Delete
                        </button>
                    </div>
                </div>

                <!-- Details Grid -->
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card radius-10 h-100 shadow-sm bg-light">
                            <div class="card-body text-center">
                                <h5 class="fw-bold">Table Name</h5>
                                <p class="text-muted mb-0">{{ $table->name }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card radius-10 h-100 shadow-sm bg-light">
                            <div class="card-body text-center">
                                <h5 class="fw-bold">Table Code</h5>
                                <p class="text-dark mb-0">{{ $table->code }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card radius-10 h-100 shadow-sm bg-light">
                            <div class="card-body text-center">
                                <h5 class="fw-bold">Table Status</h5>
                                <p class="text-dark mb-0">{{ $table->status }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Section -->
                    <div class="col-12">
                        <div class="card radius-15 border-0 shadow-lg p-4 bg-light bg-opacity-75" style="backdrop-filter: blur(10px);">
                            <div class="text-center">
                                <h4 class="fw-bold mb-4 text-primary">Table QR Code</h4>
                                <div class="p-3 d-inline-block bg-white rounded-4 shadow-sm" style="transition: transform 0.3s;">
                                    <img src="{{ asset($table->qr_path) }}"
                                         alt="QR Code"
                                         class="img-fluid"
                                         style="max-height: 240px; width: auto;">
                                </div>
                                <div class="mt-4 d-flex justify-content-center gap-4">
                                    <button class="btn btn-lg btn-outline-primary px-4"
                                            onclick="printImage('{{ asset($table->qr_path) }}')">
                                        <i class="bx bx-printer me-2"></i> Print QR
                                    </button>
                                    <a class="btn btn-lg btn-outline-success px-4"
                                       href="{{ asset($table->qr_path) }}"
                                       download>
                                        <i class="bx bx-download me-2"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>


                </div><!-- end row -->

            </div>
        </div>

    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete table <strong><span id="modalUserName"></span></strong>? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteUserForm" method="POST" action="{{ route('admin.tables.destroy', $table->id) }}">
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
    function printImage(src) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head><title>Print QR</title>
                <style>body { text-align: center; padding: 30px; } img { max-width: 100%; height: auto; }</style>
                </head>
                <body>
                    <img src="${src}" onload="window.print(); window.onafterprint = () => window.close();">
                </body>
            </html>
        `);
        printWindow.document.close();
    }

    const deleteModal = document.getElementById('deleteConfirmationModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-user-id');
            const userName = button.getAttribute('data-user-name');

            deleteModal.querySelector('#modalUserName').textContent = userName;
        });
    }
</script>
@endsection
@section('custom-css')
<style>
    .card:hover img {
        transform: scale(1.05);
        transition: transform 0.3s ease-in-out;
    }

    .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: #fff;
    }

    .btn-outline-success:hover {
        background-color: #198754;
        color: #fff;
    }
</style>
@endsection

