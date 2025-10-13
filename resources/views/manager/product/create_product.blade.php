@extends('admin.layouts.app')

@section('content')
@include('admin.components.left_sidebar')
@include('admin.components.header')
@include('admin.components.right_sidebar')

<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Product Management</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><i class="bx bx-home-alt"></i></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.products.all') }}">Products</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create New Product</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container">
            <div class="main-body">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-4">Create New Product</h5>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <label for="name" class="form-label">Name</label>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required autofocus />
                                    @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <label for="code" class="form-label">Product Code</label>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="text" class="form-control @error('code') is-invalid @enderror"
                                           id="code" name="code" value="{{ old('code') }}" required />
                                    @error('code') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <label for="category_id" class="form-label">Category</label>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select id="category_id" name="category_id"
                                            class="select2 form-control @error('category_id') is-invalid @enderror" required>
                                        <option value="">-- Select Category --</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- Actual Price (base) --}}
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <label for="actual_price" class="form-label">Actual Price (MMK)</label>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="number" min="0" class="form-control @error('actual_price') is-invalid @enderror"
                                           id="actual_price" name="actual_price" value="{{ old('actual_price') ?? old('price') }}" required />
                                    @error('actual_price') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- Discount toggle --}}
                            <div class="row mb-2">
                                <div class="col-sm-3">
                                    <label class="form-label">Discount</label>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="has_discount"
                                               name="has_discount" value="1"
                                               {{ old('has_discount') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="has_discount">Has discount</label>
                                    </div>
                                </div>
                            </div>

                            {{-- Discount details (shown only if has_discount) --}}
                            <div id="discount_fields" class="row mb-3" style="display:none;">
                                <div class="col-sm-3">
                                    <label class="form-label">Discount Type & Value</label>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <select id="discount_type" name="discount_type"
                                                    class="form-select @error('discount_type') is-invalid @enderror">
                                                <option value="percent" {{ old('discount_type','percent') === 'percent' ? 'selected' : '' }}>Percent (%)</option>
                                                <option value="fixed"   {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>Fixed (MMK)</option>
                                            </select>
                                            @error('discount_type') <div class="text-danger">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <input type="number" min="0" id="discount_value" name="discount_value"
                                                   value="{{ old('discount_value') }}"
                                                   class="form-control @error('discount_value') is-invalid @enderror"
                                                   placeholder="e.g. 10 or 500" />
                                            @error('discount_value') <div class="text-danger">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        Percent: 0–100. Fixed: MMK amount subtracted from Actual Price.
                                    </small>
                                </div>
                            </div>

                            {{-- Final price preview (auto) + hidden price field sent to server --}}
                            <input type="hidden" id="price" name="price" value="{{ old('price') }}">
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <label for="price_preview" class="form-label">Final Price (auto)</label>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="number" id="price_preview" class="form-control" value="{{ old('price') }}" readonly />
                                    <small class="text-muted">Calculated as <code>Actual Price − Discount</code></small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <label for="qty" class="form-label">Quantity</label>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="number" class="form-control @error('qty') is-invalid @enderror"
                                           id="qty" name="qty" value="{{ old('qty') }}" required />
                                    @error('qty') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <label for="description" class="form-label">Description</label>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                              id="description" name="description" required
                                              placeholder="Description.....">{{ old('description') }}</textarea>
                                    @error('description') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <label for="image-upload" class="form-label">Image</label>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="file" id="image-upload" name="image" class="form-control"
                                           accept="image/*" required />
                                    <img id="preview" src="#" alt="Image preview"
                                         style="display:none; max-width: 200px; margin-top: 10px;" />
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <button type="submit" class="btn btn-primary px-4">Create Product</button>
                                    <a href="{{ route('admin.products.all') }}" class="btn btn-secondary px-4">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div><!--/card-->
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-js')
<script>
    $(document).ready(function () {
        // select2 init
        $('.select2').each(function () {
            $(this).select2({
                theme: 'bootstrap4',
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
                allowClear: Boolean($(this).data('allow-clear')),
            });
        });

        // Preview image
        $('#image-upload').on('change', function (e) {
            const [file] = e.target.files || [];
            if (file) {
                $('#preview').attr('src', URL.createObjectURL(file)).show();
            }
        });

        // --- Discount UI + price compute ---
        const $has = $('#has_discount');
        const $box = $('#discount_fields');
        const $type = $('#discount_type');
        const $val  = $('#discount_value');
        const $base = $('#actual_price');
        const $prev = $('#price_preview');
        const $hidden = $('#price'); // value sent to server

        function clamp(n, min, max){ n = parseInt(n||0,10); return Math.max(min, Math.min(max, isNaN(n)?0:n)); }

        function computeFinal(){
            let base = clamp($base.val(), 0, 2147483647);
            let final = base;

            if ($has.is(':checked')) {
                const t = $type.val();
                let v = clamp($val.val(), 0, 2147483647);

                if (t === 'percent') {
                    v = clamp(v, 0, 100);
                    const disc = Math.floor(base * v / 100);
                    final = base - disc;
                } else if (t === 'fixed') {
                    const disc = Math.min(base, v);
                    final = base - disc;
                }
            }
            if (final < 0) final = 0;
            $prev.val(final);
            $hidden.val(final);
        }

        function toggleBox(){ $box.toggle($has.is(':checked')); }

        $has.on('change', function(){ toggleBox(); computeFinal(); });
        $type.on('change', computeFinal);
        $val.on('input', computeFinal);
        $base.on('input', computeFinal);

        // Initial state from old() values
        toggleBox();
        computeFinal();
    });
</script>
@endsection
