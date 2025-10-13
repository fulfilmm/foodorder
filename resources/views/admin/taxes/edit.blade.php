@extends('admin.layouts.app')

@section('content')
@include('admin.components.left_sidebar')
@include('admin.components.header')
@include('admin.components.right_sidebar')

<div class="page-wrapper">
  <div class="page-content">
    <h3>Edit Tax</h3>

    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
      </div>
    @endif

    <div class="card mt-3">
      <div class="card-body">
        <form method="POST" action="{{ route('admin.taxes.update', $tax->id) }}" class="row g-3">
          @csrf @method('PUT')

          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input name="name" type="text" class="form-control"
                   value="{{ old('name',$tax->name) }}" required>
          </div>

          <div class="col-md-3">
            <label class="form-label">Percent (%)</label>
            <input name="percent" type="number" min="0" max="100" step="0.01"
                   class="form-control" value="{{ old('percent',$tax->percent) }}" required>
          </div>

          <div class="col-md-3 d-flex align-items-end gap-3">
            <div class="form-check me-3">
              <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                {{ old('is_active', $tax->is_active) ? 'checked' : '' }}>
              <label class="form-check-label" for="is_active">Active</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default"
                {{ old('is_default', $tax->is_default) ? 'checked' : '' }}>
              <label class="form-check-label" for="is_default">Default</label>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">Description</label>
            <input name="description" type="text" class="form-control"
                   value="{{ old('description',$tax->description) }}">
          </div>

          <div class="col-12">
            <a href="{{ route('admin.taxes.index') }}" class="btn btn-light">Back</a>
            <button class="btn btn-primary">Update</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>
@endsection
