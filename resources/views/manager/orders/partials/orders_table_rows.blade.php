@foreach($orders as $order)
    @php
        $statusClassMap = [
            'pending'   => 'bg-light-warning text-success',
            'confirmed' => 'bg-light-info text-success',
            'preparing' => 'bg-light-secondary text-success',
            'delivered' => 'bg-light-primary text-primary',
            'eating'    => 'bg-primary text-white',
            'done'      => 'bg-success text-white',
            'canceled'  => 'bg-danger text-white',
        ];
        $badgeClass = $statusClassMap[$order->status] ?? 'bg-secondary text-white';

        $isAddon = !is_null($order->parent_order_id);
        // If you have relation ->parent, use that for nicer label; otherwise fall back to id
        $parentNo = optional($order->parent ?? null)->order_no ?? $order->parent_order_id;
    @endphp

    <tr data-id="{{ $order->id }}"
        data-kind="{{ $isAddon ? 'addon' : 'main' }}"
        data-parent-id="{{ $order->parent_order_id ?? '' }}">
        <td>
            <div class="d-flex align-items-center gap-2">
                <span class="fw-semibold">#{{ $order->order_no }}</span>
                @if($isAddon)
                    <span class="badge bg-secondary">Add-on</span>
                    @if($parentNo)
                      <small class="text-muted">of #{{ $parentNo }}</small>
                    @endif
                @else
                    <span class="badge bg-primary">Main</span>
                @endif>
            </div>
        </td>

        <td>
            <div class="badge rounded-pill text-success bg-light-{{ $order->order_type === 'takeaway' ? 'success' : 'primary' }}">
                {{ ucfirst($order->order_type) }}
            </div>
        </td>

        <td>
            <div class="badge text-success rounded-pill bg-light-{{ $order->table_id ? 'primary' : 'secondary' }}">
                {{ $order->table->name ?? 'No' }}
            </div>
        </td>

        <td>{{ $order->customer->name ?? 'Guest' }}</td>
        <td>{{ $order->created_at->format('d M Y h:i A') }}</td>

        <td>
            <div class="badge text-success bg-light-{{ $order->has_add_on ? 'success' : 'danger' }}">
                {{ $order->has_add_on ? 'Yes' : 'No' }}
            </div>
        </td>

        <td>{{ $order->add_on_count }}</td>
        <td>{{ $order->items->sum('qty') }}</td>
        <td>{{ number_format($order->total) }} MMK</td>

        <td>
            <span class="status-badge badge w-100 rounded-pill {{ $badgeClass }}"
                  data-order-id="{{ $order->id }}">
                {{ ucfirst($order->status) }}
            </span>
        </td>

        <td>
            <div class="d-flex align-items-center gap-2">
                <select class="form-select form-select-sm change-status" data-id="{{ $order->id }}">
                    @foreach(['pending','confirmed','preparing','delivered','eating','done','canceled'] as $st)
                        <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>
                            {{ ucfirst($st) }}
                        </option>
                    @endforeach
                </select>

                <a href="{{ route('manager.orders.slip', [$order->id, 'print' => 1, 'paper' => '80', 'mode' => 'detailed']) }}" class="btn btn-sm btn-success">
                    Voucher Print
                </a>
                <a href="{{ route('manager.orders.show',[$order->id]) }}" class="btn btn-sm btn-outline-info">
                    <i class="bx bx-show"></i>
                </a>
            </div>
        </td>
    </tr>
@endforeach
