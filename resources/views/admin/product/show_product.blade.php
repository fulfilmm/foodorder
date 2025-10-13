@extends('admin.layouts.app')

@section('content')
@include('admin.components.left_sidebar')
@include('admin.components.header')
@include('admin.components.right_sidebar')

@php
    // --- Pricing calc (server-side safe) ---
    $actual       = (int) ($product->actual_price ?? $product->price ?? 0); // fallback if old data
    $hasDiscount  = (bool) ($product->has_discount ?? false);
    $discountType = $product->discount_type ?? null;   // 'percent' | 'fixed' | null
    $discountVal  = is_null($product->discount_value) ? null : (int) $product->discount_value;

    $discountAmt  = 0;
    if ($hasDiscount && !is_null($discountVal)) {
        if ($discountType === 'percent') {
            $p = max(0, min(100, $discountVal));
            $discountAmt = (int) floor($actual * $p / 100);
        } elseif ($discountType === 'fixed') {
            $discountAmt = min($actual, max(0, $discountVal));
        }
    }
    $finalPrice = max(0, $actual - $discountAmt);
@endphp

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="page-content">

        <!-- Breadcrumb -->
        <div class="page-breadcrumb d-flex align-items-center mb-4">
            <div class="breadcrumb-title pe-3">Product Management</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0 bg-transparent">
                        <li class="breadcrumb-item"><i class="bx bx-home-alt"></i></li>
                        <li class="breadcrumb-item active">Product Detail</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card shadow-sm radius-10">
            <div class="card-body p-4">

                <!-- Header Section -->
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-uppercase">{{ $product->name }}</h2>
                    <p class="fw-bold">Product Code - {{ $product->code }}</p>

                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-outline-primary">
                            <i class="bx bx-edit-alt"></i> Edit
                        </a>
                        <button class="btn btn-outline-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteConfirmationModal"
                            data-user-id="{{ $product->id }}"
                            data-user-name="{{ $product->name }}">
                            <i class="bx bx-trash"></i> Delete
                        </button>
                    </div>
                </div>

                <!-- Details Grid -->
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card radius-10 h-100 shadow-sm bg-light">
                            <div class="card-body text-center">
                                <h5 class="fw-bold">Product Name</h5>
                                <hr>
                                <p class="text-muted mb-0">{{ $product->name }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card radius-10 h-100 shadow-sm bg-light">
                            <div class="card-body text-center">
                                <h5 class="fw-bold">Category</h5>
                                <hr>
                                <p class="text-dark mb-0">
                                    {{ optional($product->category)->name ?? '—' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Price Card (Final + strike-through actual if discounted) -->
                    <div class="col-md-4">
                        <div class="card radius-10 h-100 shadow-sm bg-light">
                            <div class="card-body text-center">
                                <h5 class="fw-bold">Product Price</h5>
                                <hr>
                                <div class="mb-1">
                                    @if($hasDiscount && $discountAmt > 0)
                                        <span class="badge bg-success rounded-pill">Discounted</span>
                                        @if($discountType === 'percent')
                                            <span class="badge bg-primary rounded-pill">-{{ $discountVal }}%</span>
                                        @else
                                            <span class="badge bg-primary rounded-pill">-{{ number_format($discountVal) }} MMK</span>
                                        @endif
                                    @endif
                                </div>
                                <p class="text-dark mb-0 fs-5 fw-bold">
                                    {{ number_format($finalPrice) }} MMK
                                </p>
                                @if($hasDiscount && $discountAmt > 0)
                                    <p class="mb-0">
                                        <s class="text-muted">{{ number_format($actual) }} MMK</s>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Qty / Sold / Remaining -->
                    <div class="col-md-4">
                        <div class="card radius-10 h-100 shadow-sm bg-light">
                            <div class="card-body text-center">
                                <h5 class="fw-bold">Quantity</h5>
                                <hr>
                                <p class="text-dark mb-0">{{ number_format($product->qty) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card radius-10 h-100 shadow-sm bg-light">
                            <div class="card-body text-center">
                                <h5 class="fw-bold">Total Sold</h5>
                                <hr>
                                <p class="text-dark mb-0">{{ number_format($product->sell_qty) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card radius-10 h-100 shadow-sm bg-light">
                            <div class="card-body text-center">
                                <h5 class="fw-bold">Remaining</h5>
                                <hr>
                                <p class="text-dark mb-0">{{ number_format($product->remain_qty) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing breakdown -->
                    <div class="col-md-6">
                        <div class="card radius-10 h-100 shadow-sm bg-light">
                            <div class="card-body">
                                <h5 class="fw-bold text-center">Pricing</h5>
                                <hr>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Original Price</span>
                                    <span>{{ number_format($actual) }} MMK</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Has Discount</span>
                                    <span>
                                        @if($hasDiscount && $discountAmt > 0)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </span>
                                </div>
                                @if($hasDiscount)
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Discount Type</span>
                                        <span class="text-capitalize">{{ $discountType ?? '—' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Discount Value</span>
                                        <span>
                                            @if($discountType === 'percent')
                                                {{ (int) $discountVal }}%
                                            @elseif($discountType === 'fixed')
                                                {{ number_format((int) $discountVal) }} MMK
                                            @else
                                                —
                                            @endif
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Discount Amount</span>
                                        <span>-{{ number_format($discountAmt) }} MMK</span>
                                    </div>
                                @endif
                                <hr class="my-2">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">Final Price</span>
                                    <span class="fw-bold">{{ number_format($finalPrice) }} MMK</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="col-md-6">
                        <div class="card radius-10 h-100 shadow-sm bg-light">
                            <div class="card-body">
                                <h5 class="fw-bold text-center">Description</h5>
                                <hr>
                                <p class="text-dark mb-0">{{ $product->description ?? '—' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Image Section -->
                    <div class="col-12">
                        <div class="card radius-15 border-0 shadow-lg p-4 bg-light bg-opacity-75" style="backdrop-filter: blur(10px);">
                            <div class="text-center">
                                <h4 class="fw-bold mb-4 text-primary">Product Image</h4>
                                <div class="p-3 d-inline-block bg-white rounded-4 shadow-sm" style="transition: transform 0.3s;">
                                    @if($product->image)
                                        <img src="{{ asset($product->image) }}"
                                             alt="{{ $product->name }}"
                                             class="img-fluid"
                                             style="max-height: 240px; width: auto;">
                                    @else
                                        <div class="text-muted">No image uploaded</div>
                                    @endif
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
                Are you sure you want to delete product <strong><span id="modalUserName"></span></strong>? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteUserForm" method="POST" action="{{ route('admin.products.destroy', $product->id) }}">
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
        const w = window.open('', '_blank');
        w.document.write(`
            <html>
                <head><title>Print Image</title>
                <style>body { text-align: center; padding: 30px; } img { max-width: 100%; height: auto; }</style>
                </head>
                <body>
                    <img src="${src}" onload="window.print(); window.onafterprint = () => window.close();">
                </body>
            </html>
        `);
        w.document.close();
    }

    const deleteModal = document.getElementById('deleteConfirmationModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const name = button.getAttribute('data-user-name');
            deleteModal.querySelector('#modalUserName').textContent = name;
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
    .btn-outline-primary:hover { background-color: #0d6efd; color: #fff; }
    .btn-outline-success:hover { background-color: #198754; color: #fff; }
</style>
@endsection
