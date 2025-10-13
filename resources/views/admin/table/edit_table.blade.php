@extends('admin.layouts.app')

@section('content')
@include('admin.components.left_sidebar')
@include('admin.components.header')
@include('admin.components.right_sidebar')

<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Table Management</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><i class="bx bx-home-alt"></i></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.tables.all') }}">Tables</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Table</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container">
            <div class="main-body">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-4">Edit Table</h5>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.tables.update', $table->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <label for="name" class="form-label">Name</label>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name', $table->name) }}"
                                           required autofocus>
                                    @error('name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <label class="form-label">Code</label>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="text" class="form-control" value="{{ $table->code }}" disabled>
                                    <small class="text-muted">Code is auto-generated and cannot be changed.</small>
                                </div>
                            </div>

                            {{-- Optional: preview current QR --}}
                            <div class="row mb-4">
                                <div class="col-sm-3">
                                    <label class="form-label">Current QR</label>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <img src="{{ asset($table->qr_path) }}" alt="QR" style="max-height:160px">
                                    <div class="form-text">QR is regenerated automatically if you change the name.</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <button type="submit" class="btn btn-primary px-4">Update Table</button>
                                    <a href="{{ route('admin.tables.show', $table->id) }}" class="btn btn-secondary px-4">Cancel</a>
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
