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
                        <li class="breadcrumb-item active" aria-current="page">Create New Table</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="container">
            <div class="main-body">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-4">Create New Table</h5>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('admin.tables.store') }}" method="POST">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <label for="name" class="form-label">Name</label>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus />
                                    @error('name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <button type="submit" class="btn btn-primary px-4">Create Table</button>
                                    <a href="{{ route('admin.users.admin') }}" class="btn btn-secondary px-4">Cancel</a>
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

@section('custom-js')

@endsection
