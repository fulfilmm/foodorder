@extends('admin.layouts.app')

@section('custom-css')
<style type="text/css">
  .recent-product-img-pr{
      width: 70px;
      height: 70px;
      background-color: #fbfbfb;
      border-radius: 10px;
      border: 1px solid #e6e6e6;
      display: flex; align-items: center; justify-content: center;
      overflow: hidden;
  }
  .recent-product-img-pr img { max-width: 100%; max-height: 100%; object-fit: cover; }
  .price-final { font-weight: 700; }
  .price-actual { color:#6c757d; text-decoration: line-through; font-size: 12px; }
  .badge-discount { font-size: 11px; }
  td .desc { max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
@endsection

@section('content')
@include('admin.components.left_sidebar')
@include('admin.components.header')
@include('admin.components.right_sidebar')

<div class="page-wrapper">
  <div class="page-content">
    <h3>Product Management</h3>
    <h3 class="mt-5">All Products</h3>

    <div class="card">
      <div class="card-body">
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm mb-3">+ Create Product</a>

        <div class="table-responsive">
          <table id="productsTable" class="table table-striped table-bordered align-middle">
            <thead>
              <tr>
                <th style="width:90px">Image</th>
                <th>Name</th>
                <th>Code</th>
                <th>Category</th>
                <th>Description</th>
                <th>Pricing (MMK)</th>
                <th>Qty</th>
                <th>Sold</th>
                <th>Remain</th>
                <th>Created</th>
                <th style="width:180px">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($products as $product)
                @php
                  // Safe pricing calc (in case accessors aren’t set up)
                  $actual       = (int) ($product->actual_price ?? $product->price ?? 0);
                  $hasDiscount  = (bool) ($product->has_discount ?? false);
                  $discountType = $product->discount_type ?? null; // 'percent'|'fixed'|null
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
                  // final price column prefers stored price if you keep it synced
                  $final = (int) ($product->price ?? max(0, $actual - $discountAmt));
                @endphp
                <tr>
                  <td>
                    <div class="recent-product-img-pr">
                      <img src="{{ $product->image ? asset($product->image) : asset('assets/images/placeholder.png') }}" alt="{{ $product->name }}">
                    </div>
                  </td>
                  <td>{{ $product->name }}</td>
                  <td>{{ $product->code }}</td>
                  <td>{{ optional($product->category)->name ?? '—' }}</td>
                  <td><span class="desc" title="{{ $product->description }}">{{ $product->description }}</span></td>

                  <!-- Pricing cell -->
                  <td data-order="{{ $final }}">
                    <div class="price-final">{{ number_format($final) }} Ks</div>
                    @if($hasDiscount && $discountAmt > 0)
                      <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="badge bg-success badge-discount">Discount</span>
                        @if($discountType === 'percent')
                          <span class="badge bg-primary badge-discount">-{{ (int)$discountVal }}%</span>
                        @else
                          <span class="badge bg-primary badge-discount">-{{ number_format((int)$discountVal) }}</span>
                        @endif
                      </div>
                      <div class="price-actual">{{ number_format($actual) }} Ks</div>
                    @endif
                  </td>

                  <td>{{ number_format($product->qty) }}</td>
                  <td>{{ number_format($product->sell_qty) }}</td>
                  <td>{{ number_format($product->remain_qty) }}</td>
                  <td>{{ optional($product->created_at)->setTimezone(config('app.timezone'))->format('Y-m-d H:i') }}</td>

                  <td>
                    <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-success btn-sm">View</a>
                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary btn-sm">Edit</a>
                    <button type="button" class="btn btn-danger btn-sm"
                      data-bs-toggle="modal"
                      data-bs-target="#deleteConfirmationModal"
                      data-product-id="{{ $product->id }}"
                      data-product-name="{{ $product->name }}">
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
        Are you sure you want to delete <strong><span id="modalProductName"></span></strong>? This action cannot be undone.
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form id="deleteProductForm" method="POST" action="">
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
    const table = $('#productsTable').DataTable({
      lengthChange: false,
      ordering: true,
      columnDefs: [
        { orderable: false, targets: [0, 4, 10] }, // image, description, action not orderable
      ],
      buttons: [
        {
          extend: 'copyHtml5',
          exportOptions: { columns: ':visible:not(:last-child)' }
        },
        {
          extend: 'excelHtml5',
          exportOptions: { columns: ':visible:not(:last-child)' }
        },
        {
          extend: 'pdfHtml5',
          exportOptions: { columns: ':visible:not(:last-child)' }
        },
        {
          extend: 'print',
          exportOptions: { columns: ':visible:not(:last-child)' }
        }
      ]
    });
    table.buttons().container().appendTo('#productsTable_wrapper .col-md-6:eq(0)');

    // Delete modal wiring
    const deleteModal = document.getElementById('deleteConfirmationModal');
    if (deleteModal) {
      deleteModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-product-id');
        const name = button.getAttribute('data-product-name');
        deleteModal.querySelector('#modalProductName').textContent = name;
        deleteModal.querySelector('#deleteProductForm').action = `/admin/products/${id}`;
      });
    }
  });
</script>
@endsection
