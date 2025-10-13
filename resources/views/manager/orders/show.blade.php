@extends('manager.layouts.app')

@section('custom-css')
  <style>
    .step      { width: 16.6%; text-align:center }
    .step .dot { width:12px;height:12px;border-radius:999px;display:inline-block;background:#e5e7eb }
    .step.done .dot   { background:#22c55e }
    .step.active .dot { background:#0ea5e9 }
    .step .label { font-size:12px }
    .chip{font-size:11px;border:1px solid #e5e7eb;border-radius:999px;padding:.125rem .5rem;background:#f8fafc}
    .sticky-actions{position:sticky;top:-1px;z-index:5;background:#fff;border-bottom:1px solid #eef2f7}
    .table > :not(caption) > * > *{vertical-align:middle}
  </style>
@endsection

@section('content')
  @include('manager.components.left_sidebar')
  @include('manager.components.header')
  @include('manager.components.right_sidebar')

  <div class="page-wrapper">
    <div class="page-content">

      <div class="card">
        {{-- Top actions --}}
        <div class="card-body p-3 sticky-actions d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <a href="{{ route('manager.orders.all')}}" class="btn btn-outline-secondary btn-sm">← Back</a>
            <div>
              <div class="fw-bold fs-5 mb-0">
                Order #{{ $root->order_no }}
                @if(($addons ?? collect())->count())
                  <small class="text-muted">
                    (+ {{ $addons->count() }} add-on{{ $addons->count() > 1 ? 's' : '' }})
                  </small>
                @endif
              </div>
              <div class="text-muted small">
                {{ $root->order_type === 'dine_in' ? 'Dine-in' : 'Takeaway' }}
                @if($root->table) • Table: <b>{{ $root->table->name }}</b>@endif
                • Created: {{ $root->created_at?->format('d M Y h:i A') }}
              </div>
            </div>
          </div>

          <div class="d-flex gap-2">
            <a class="btn btn-outline-dark btn-sm"
               href="{{ route('manager.orders.slip', [$root->id, 'print' => 1, 'paper' => '80']) }}"
               target="_blank">
              <i class="bx bx-printer"></i> Print Slip
            </a>

            @if(!empty($nextLabel))
              <button class="btn btn-success btn-sm" id="btn-next"
                      onclick="nextStatus({{ $root->id }})">{{ $nextLabel }}</button>
            @endif

            @if(!empty($canCancel) && $canCancel)
              <button class="btn btn-danger btn-sm" id="btn-cancel"
                      onclick="cancelOrder({{ $root->id }})">Cancel</button>
            @endif
          </div>
        </div>

        {{-- Status + summary --}}
        <div class="row g-3 px-3 pt-3">
          <div class="col-12 col-lg-8">
            <div class="p-3 border rounded-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="small text-muted">Status</div>
                <span class="badge
                  @switch($root->status)
                    @case('pending') bg-warning text-dark @break
                    @case('confirmed') bg-info text-dark @break
                    @case('preparing') bg-secondary @break
                    @case('delivered') bg-primary @break
                    @case('eating') bg-dark @break
                    @case('done') bg-success @break
                    @case('canceled') bg-danger @break
                    @default bg-light text-dark
                  @endswitch">
                  {{ ucfirst($root->status) }}
                </span>
              </div>

              @php
                $steps = ['pending','confirmed','preparing','delivered','eating','done'];
                $currentIndex = array_search($root->status, $steps, true);
              @endphp
              <div class="d-flex justify-content-between">
                @foreach($steps as $i => $s)
                  @php
                    $isDone = $i < $currentIndex;
                    $isActive = $i === $currentIndex;
                  @endphp
                  <div class="step {{ $isDone ? 'done' : '' }} {{ $isActive ? 'active' : '' }}">
                    <div class="dot mb-1"></div>
                    <div class="label text-capitalize">{{ $s }}</div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-4">
            <div class="p-3 border rounded-3">
              <div class="small text-muted mb-2">Order summary</div>
              <div class="d-flex justify-content-between">
                <span>Orders</span>
                <span class="fw-semibold text-end ms-2" style="max-width: 220px;">
                  {{ ($allOrders ?? collect())->pluck('order_no')->implode(' + ') }}
                </span>
              </div>
              <div class="d-flex justify-content-between mt-1">
                <span>Customer</span>
                <span>{{ $root->customer->name ?? 'Guest' }}</span>
              </div>
              <div class="d-flex justify-content-between mt-1">
                <span>Pickup</span>
                <span>{{ $root->pickup_time ?: '—' }}</span>
              </div>
            </div>
          </div>
        </div>

        {{-- Tabs --}}
        <div class="px-3 pt-3">
          <ul class="nav nav-pills mb-3" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tabCombined" type="button" role="tab">
                Combined Items
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tabPerOrder" type="button" role="tab">
                Per-Order
              </button>
            </li>
          </ul>

          <div class="tab-content">
            {{-- Combined --}}
            <div class="tab-pane fade show active" id="tabCombined" role="tabpanel">
              <div class="table-responsive border rounded-3">
                <table class="table align-middle mb-0">
                  <thead class="table-light">
                  <tr>
                    <th style="width:38%">Item</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Line</th>
                    <th>From</th>
                  </tr>
                  </thead>
                  <tbody>
                  @forelse(($combinedItems ?? []) as $row)
                    <tr>
                      <td>
                        <div class="fw-semibold">{{ $row['name'] }}</div>
                        @if(!empty($row['comment']))
                          <div class="text-muted small fst-italic">* {{ $row['comment'] }}</div>
                        @endif
                      </td>
                      <td class="fw-semibold">{{ $row['qty'] }}</td>
                      <td>{{ number_format($row['price']) }} MMK</td>
                      <td class="fw-semibold">{{ number_format($row['line_total']) }} MMK</td>
                      <td><span class="chip">#{{ $row['order_no'] }}</span></td>
                    </tr>
                  @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No items.</td></tr>
                  @endforelse
                  </tbody>
                </table>
              </div>
            </div>

            {{-- Per-Order --}}
            <div class="tab-pane fade" id="tabPerOrder" role="tabpanel">
              @foreach(($allOrders ?? collect()) as $o)
                <div class="border rounded-3 mb-3">
                  <div class="p-3 border-bottom d-flex justify-content-between">
                    <div>
                      <div class="fw-semibold">Order #{{ $o->order_no }}</div>
                      <div class="text-muted small">
                        {{ ucfirst($o->status) }} • {{ $o->created_at?->format('d M Y h:i A') }}
                      </div>
                    </div>
                    <div>
                      <span class="chip">{{ $o->order_type === 'dine_in' ? 'Dine-in' : 'Takeaway' }}</span>
                      @if($o->table) <span class="chip">Table: {{ $o->table->name }}</span>@endif
                    </div>
                  </div>
                  <div class="p-0 table-responsive">
                    <table class="table mb-0">
                      <thead class="table-light">
                      <tr><th>Item</th><th>Qty</th><th>Unit</th><th>Line</th></tr>
                      </thead>
                      <tbody>
                      @foreach($o->items as $it)
                        <tr>
                          <td>
                            <div class="fw-semibold">{{ $it->name ?? optional($it->product)->name }}</div>
                            @if(!empty($it->comment))
                              <div class="text-muted small fst-italic">* {{ $it->comment }}</div>
                            @endif
                          </td>
                          <td class="fw-semibold">{{ $it->qty }}</td>
                          <td>{{ number_format($it->price) }} MMK</td>
                          <td class="fw-semibold">{{ number_format($it->price * $it->qty) }} MMK</td>
                        </tr>
                      @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>

        {{-- Totals & Taxes + Timeline --}}
        <div class="row g-3 px-3 pb-4">
          <div class="col-12 col-lg-6">
            <div class="p-3 border rounded-3">
              <div class="d-flex justify-content-between">
                <span class="text-muted">Subtotal</span>
                <span class="fw-semibold">{{ number_format($subtotal ?? 0) }} MMK</span>
              </div>
              <div class="d-flex justify-content-between mt-1">
                <span class="text-muted">Tax <small>({{ $taxPercent ?? 0 }}%)</small></span>
                <span class="fw-semibold">{{ number_format($taxAmount ?? 0) }} MMK</span>
              </div>
              <hr class="my-2">
              <div class="d-flex justify-content-between fs-5">
                <span class="fw-bold">Total</span>
                <span class="fw-bold">{{ number_format($total ?? 0) }} MMK</span>
              </div>
              <div class="mt-2 d-flex flex-wrap gap-1">
                @forelse(($activeTaxes ?? []) as $t)
                  <span class="chip">{{ $t->name }} {{ (float)$t->percent }}%</span>
                @empty
                  <span class="chip">No tax</span>
                @endforelse
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="p-3 border rounded-3">
              <div class="small text-muted mb-2">Timeline</div>
              <ul class="list-unstyled mb-0">
                @forelse(($timeline ?? []) as $t)
                  <li class="d-flex justify-content-between border-bottom py-1">
                    <span class="text-capitalize">{{ $t['status'] }}</span>
                    <span class="text-muted small">{{ $t['when'] }}</span>
                  </li>
                @empty
                  <li class="text-muted small">No history.</li>
                @endforelse
              </ul>
            </div>
          </div>
        </div>

      </div> {{-- /card --}}

    </div>
  </div>
@endsection

@section('custom-js')
<script>
  function nextStatus(orderId){
    const btn = document.getElementById('btn-next');
    if (!btn) return;
    btn.disabled = true; btn.innerText = 'Updating...';

    fetch(`/data/orders/${orderId}/status`, {
      method:'PATCH',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
      body: JSON.stringify({})
    })
    .then(r=>r.json())
    .then(j=>{
      if(j?.success){ location.reload(); }
      else { alert(j?.message || 'Update failed'); btn.disabled=false; btn.innerText='Retry'; }
    })
    .catch(()=>{ alert('Request failed'); btn.disabled=false; btn.innerText='Retry';});
  }

  function cancelOrder(orderId){
    if(!confirm('Cancel this order?')) return;
    const btn = document.getElementById('btn-cancel');
    if (btn) { btn.disabled = true; btn.innerText = 'Cancelling...'; }

    fetch(`/data/orders/${orderId}/cancel`, {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
      body: JSON.stringify({})
    })
    .then(r=>r.json())
    .then(j=>{
      if(j?.success){ location.reload(); }
      else { alert(j?.message || 'Cancel failed'); if(btn){ btn.disabled=false; btn.innerText='Cancel'; } }
    })
    .catch(()=>{ alert('Request failed'); if(btn){ btn.disabled=false; btn.innerText='Cancel'; }});
  }
</script>
@endsection
