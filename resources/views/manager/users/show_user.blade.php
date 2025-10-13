@extends('manager.layouts.app')

@section('content')
@include('manager.components.left_sidebar')
@include('manager.components.header')
@include('manager.components.right_sidebar')

<div class="page-wrapper">
  <div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
      <div class="breadcrumb-title pe-3">User Profile</div>
      <div class="ps-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 p-0">
            <li class="breadcrumb-item"><i class="bx bx-home-alt"></i></li>
            <li class="breadcrumb-item active" aria-current="page">User Detail</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container">
      <div class="main-body">
        <div class="row">
          {{-- Profile card --}}
          <div class="col-lg-12" id="userProfileCard">
            <div class="card">
              <div class="card-body">
                <div class="d-flex flex-column align-items-center text-center">
                  <div class="mt-3">
                    <h4>{{ $user->name }}</h4>
                    <p class="text-secondary mb-1">{{ $user->role }} role</p>
                    <p class="text-muted font-size-sm">{{ $user->email }}</p>
                  </div>
                </div>
                <hr class="my-4" />
                <ul class="list-group list-group-flush">
                  <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                    <h6 class="mb-0">Name</h6>
                    <span class="text-secondary">{{ $user->name }}</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                    <h6 class="mb-0">Role</h6>
                    <span class="text-secondary">{{ $user->role }}</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                    <h6 class="mb-0">Email</h6>
                    <span class="text-secondary">{{ $user->email }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>



          {{-- CUSTOMER ORDERS --}}
          @if($user->role === 'customer')
          <div class="col-lg-12 mt-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                  <h5 class="mb-0">Order History</h5>

                  <form method="GET" class="d-flex gap-2">
                    <input type="text"
                      name="q"
                      value="{{ request('q') }}"
                      class="form-control form-control-sm"
                      placeholder="Search by order no or item name" />
                    <button class="btn btn-sm btn-outline-secondary" type="submit">Search</button>
                    @if(request('q'))
                      <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-light">Clear</a>
                    @endif
                  </form>
                </div>

                {{-- Quick stats --}}
                <div class="row g-2 mb-3">
                  <div class="col-6 col-md-3">
                    <div class="p-2 border rounded">
                      <div class="text-muted small">Total Orders</div>
                      <div class="fw-bold">{{ method_exists($orders,'total') ? $orders->total() : $orders->count() }}</div>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="p-2 border rounded">
                      <div class="text-muted small">Lifetime Spend</div>
                      <div class="fw-bold">{{ number_format($lifetimeSpend ?? 0) }} MMK</div>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="p-2 border rounded">
                      <div class="text-muted small">Last Order</div>
                      <div class="fw-bold">{{ $lastOrderAt ? $lastOrderAt->format('d M Y h:i A') : '—' }}</div>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="p-2 border rounded">
                      <div class="text-muted small">By Status</div>
                      <div class="small">
                        @forelse($statusCounts as $s => $c)
                          <span class="badge bg-light text-dark border me-1 mb-1">{{ ucfirst($s) }}: {{ $c }}</span>
                        @empty
                          <span class="text-muted">No orders</span>
                        @endforelse
                      </div>
                    </div>
                  </div>
                </div>

                {{-- Orders list --}}
                @forelse($orders as $o)
                  <div class="border rounded p-3 mb-2">
                    <div class="d-flex justify-content-between flex-wrap">
                      <div>
                        <div class="fw-semibold">
                          <i class="bx bx-purchase-tag-alt me-1"></i> #{{ $o->order_no }}
                        </div>
                        <div class="text-muted small">
                          {{ ucfirst($o->order_type) }}
                          @if($o->table) • Table: {{ $o->table->name }} @endif
                          • {{ $o->created_at->format('d M Y h:i A') }}
                        </div>
                      </div>
                      <div class="text-end">
                        <span class="badge
                          @switch($o->status)
                            @case('pending') bg-warning @break
                            @case('confirmed') bg-info @break
                            @case('preparing') bg-secondary @break
                            @case('delivered') bg-primary @break
                            @case('eating') bg-dark @break
                            @case('done') bg-success @break
                            @case('canceled') bg-danger @break
                            @default bg-light text-dark
                          @endswitch">
                          {{ ucfirst($o->status) }}
                        </span>
                        <div class="fw-bold mt-1">{{ number_format($o->total ?? $o->items->sum(fn($i)=>$i->qty*$i->price)) }} MMK</div>
                        <button class="btn btn-sm btn-outline-secondary mt-1" type="button"
                          onclick="toggleItems({{ $o->id }})">Items</button>
                      </div>
                    </div>

                    <div id="items-{{ $o->id }}" class="mt-2" style="display:none;">
                      <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                          <tr><th>Item</th><th class="text-center" style="width:80px">Qty</th><th class="text-end" style="width:120px">Unit</th><th class="text-end" style="width:120px">Line</th></tr>
                        </thead>
                        <tbody>
                          @foreach($o->items as $it)
                            <tr>
                              <td>
                                {{ $it->name ?? optional($it->product)->name }}
                                @if(!empty($it->comment))
                                  <div class="text-muted small fst-italic">* {{ $it->comment }}</div>
                                @endif
                              </td>
                              <td class="text-center">{{ $it->qty }}</td>
                              <td class="text-end">{{ number_format($it->price) }}</td>
                              <td class="text-end">{{ number_format($it->price * $it->qty) }}</td>
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>
                @empty
                  <div class="text-muted">No orders found.</div>
                @endforelse

                {{-- Pagination --}}
                @if(method_exists($orders, 'links'))
                  <div class="mt-3">
                    {{ $orders->links() }}
                  </div>
                @endif

              </div>
            </div>
          </div>
          @endif
          {{-- /CUSTOMER ORDERS --}}

        </div>
      </div>
    </div>
  </div>
</div>

{{-- Delete modal --}}
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
      Are you sure you want to delete the user <b><span id="modalUserName"></span></b>?
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      <form id="deleteUserForm" method="POST" action="">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger">Yes, Delete</button>
      </form>
    </div>
  </div></div>
</div>
@endsection

@section('custom-js')
<script>
  function editUser(){
    const card = document.getElementById('userProfileCard');
    const form = document.getElementById('editUserForm');
    const isHidden = form.style.display === 'none';
    form.style.display = isHidden ? 'block' : 'none';
    card.classList.toggle('col-lg-12', !isHidden);
    card.classList.toggle('col-lg-4',  isHidden);
  }

  function toggleItems(id){
    const el = document.getElementById(`items-${id}`);
    el.style.display = (el.style.display === 'none' || !el.style.display) ? 'block' : 'none';
  }

  document.addEventListener('DOMContentLoaded', function(){
    const modal = document.getElementById('deleteConfirmationModal');
    if(!modal) return;
    modal.addEventListener('show.bs.modal', function (event) {
      const button    = event.relatedTarget;
      const userId    = button.getAttribute('data-user-id');
      const userName  = button.getAttribute('data-user-name');
      document.getElementById('modalUserName').textContent = userName;
      document.getElementById('deleteUserForm').action = `/admin/users/${userId}`;
    });
  });
</script>
@endsection
