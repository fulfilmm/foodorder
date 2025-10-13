@extends('admin.layouts.app')

@section('content')
@include('admin.components.left_sidebar')
@include('admin.components.header')
@include('admin.components.right_sidebar')

<div class="page-wrapper">
  <div class="page-content">
    <h3>Tax Management</h3>

    @if(session('success'))
      <div class="alert alert-success mt-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger mt-2">{{ session('error') }}</div>
    @endif

    <div class="card mt-3">
      <div class="card-body">
        <a href="{{ route('admin.taxes.create') }}" class="btn btn-primary btn-sm mb-3">+ Create Tax</a>

        <div class="table-responsive">
          <table id="taxesTable" class="table table-striped table-bordered align-middle">
            <thead>
              <tr>
                <th>Name</th>
                <th>Percent</th>
                <th>Active</th>
                <th>Default</th>
                <th>Description</th>
                <th>Created</th>
                <th style="width:180px">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($taxes as $t)
                <tr>
                  <td>{{ $t->name }}</td>
                  <td>{{ number_format($t->percent, 2) }}%</td>
                  <td>
                    <span class="badge {{ $t->is_active ? 'bg-success' : 'bg-secondary' }}">
                      {{ $t->is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td>
                    <span class="badge {{ $t->is_default ? 'bg-primary' : 'bg-light text-dark' }}">
                      {{ $t->is_default ? 'Default' : '—' }}
                    </span>
                  </td>
                  <td>{{ $t->description }}</td>
                  <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                  <td>
                    <div class="d-flex gap-1">
                      <a href="{{ route('admin.taxes.edit', $t->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>

                      <form method="POST" action="{{ route('admin.taxes.destroy', $t->id) }}"
                            onsubmit="return confirm('Delete this tax?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" {{ $t->is_default ? 'disabled' : '' }}>
                          Delete
                        </button>
                      </form>

                      {{-- Optional quick actions --}}
                      <form method="POST" action="{{ route('admin.taxes.toggleActive',$t->id) }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-outline-secondary">
                          {{ $t->is_active ? 'Disable' : 'Enable' }}
                        </button>
                      </form>

                      @if(!$t->is_default)
                        <form method="POST" action="{{ route('admin.taxes.makeDefault',$t->id) }}">
                          @csrf @method('PATCH')
                          <button class="btn btn-sm btn-outline-dark">Make Default</button>
                        </form>
                      @endif
                    </div>
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
@endsection

@section('custom-js')
<script>
  $(function(){
    $('#taxesTable').DataTable({
      lengthChange:false,
      ordering:true,
      buttons:['copyHtml5','excelHtml5','pdfHtml5','print']
    }).buttons().container().appendTo('#taxesTable_wrapper .col-md-6:eq(0)');
  });
</script>
@endsection
