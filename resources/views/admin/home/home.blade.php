
@extends('admin.layouts.app')

@section('custom-css')
    <!-- Flatpickr for custom month -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">

    <!-- Select2 for custom year -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style type="text/css">
        .card canvas {
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-radius: 12px;
        }
    </style>
@endsection

@section('content')
    @include('admin.components.left_sidebar')
    @include('admin.components.header')
    @include('admin.components.right_sidebar')

    <div class="page-wrapper">
        <div class="page-content">
            <h1>Dashboard Welcome {{ Auth::user()->name }}</h1>
            <h3>Orders and Summary</h3>

            <select id="date-range" class="form-select w-auto mb-3">
                <option value="all">All</option>
                <option value="today">Today</option>
                <option value="weekly">This Week</option>
                <option value="monthly">This Month</option>
                <option value="yearly">This Year</option>
                <option value="custom">Custom Date Range</option>
                <option value="custom-month">Custom Month</option>
                <option value="custom-year">Custom Year</option>
            </select>

            <!-- Custom Date Range -->
            <div id="custom-date-range" class="row g-2 mb-3 d-none">
                <div class="col-md-3">
                    <input type="date" id="custom-start" class="form-control">
                </div>
                <div class="col-md-3">
                    <input type="date" id="custom-end" class="form-control">
                </div>
                <div class="col-md-3">
                    <button id="apply-custom-date" class="btn btn-primary w-100">Apply</button>
                </div>
            </div>

            <!-- Custom Month -->
            <div id="custom-month" class="row g-2 mb-3 d-none">
                <div class="col-md-3">
                    <input type="text" id="custom-month-value" class="form-control" placeholder="Choose Month">
                </div>
                <div class="col-md-3">
                    <button id="apply-custom-month" class="btn btn-primary w-100">Apply</button>
                </div>
            </div>

            <!-- Custom Year using Select2 -->
            <div id="custom-year" class="row g-2 mb-3 d-none">
                <div class="col-md-3">
                    <select id="custom-year-select" class="form-control">
                        <option value="">Choose Year</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button id="apply-custom-year" class="btn btn-primary w-100">Apply</button>
                </div>
            </div>

            <div id="stats" class="mb-4">
                <!-- Stats will render here -->
            </div>
{{--            <div class="row">--}}
{{--                <div class="col-md-6">--}}
{{--                    <canvas id="ordersChart" height="150"></canvas>--}}

{{--                </div>--}}
{{--                <div class="col-md-6">--}}
{{--                    <canvas id="salesChart" height="150"></canvas>                </div>--}}
{{--            </div>--}}
            <div class="card radius-10">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div>
                            <h5 class="mb-0">Orders Summary</h5>
                        </div>
                    </div>
{{--                    <div class="row mb-3">--}}
{{--                        <div class="col-md-3">--}}
{{--                            <select id="filter-status" class="form-select">--}}
{{--                                <option value="pending">Pending</option>--}}
{{--                                <option value="confirmed">Confirmed</option>--}}
{{--                                <option value="preparing">Preparing</option>--}}
{{--                                <option value="delivered">Delivered</option>--}}
{{--                                <option value="eating">Eating</option>--}}
{{--                                <option value="done">Done</option>--}}
{{--                                <option value="canceled">Canceled</option>--}}
{{--                            </select>--}}
{{--                        </div>--}}
{{--                        <div class="col-md-3">--}}
{{--                            <select id="filter-order-type" class="form-select">--}}
{{--                                <option value="takeaway">Takeaway</option>--}}
{{--                                <option value="dine_in">Dine In</option>--}}
{{--                            </select>--}}
{{--                        </div>--}}
{{--                    </div>--}}

                    <div class="table-responsive">
                        <table id="orders-table" class="table table-bordered">
                            <thead class="table-light">
                            <tr>
                                <th>Order No</th>
                                <th>Order Type</th>
                                <th>Table Number</th>
                                <th>Customer</th>
                                <th>Created At</th>
                                <th>Has Add-on</th>
                                <th>Add-On Order Count</th>
                                <th>Product Count</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($orders as $order)
{{--                                @dd($order,$order->created_at,$order->created_at->format('d M Y h:i A'))--}}
                                <tr>
                                    <td>#{{ $order->order_no }}</td>
                                    <td>
                                        <div class="badge rounded-pill text-success bg-light-{{ $order->order_type === 'takeaway' ? 'success' : 'primary' }}">
                                            {{ ucfirst($order->order_type) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="badge rounded-pill text-success bg-light-{{ $order->table_id ? 'primary' : 'success' }}">
                                            {{ $order->table->name ?? 'No' }}
                                        </div>
                                    </td>
                                    <td>{{ $order->customer->name ?? 'Guest' }}</td>
                                    <td>{{ $order->created_at->format('d M Y h:i A') }}</td>
                                    <td>
                                        <div class=" rounded-pill text-success w-80 badge bg-light-{{ $order->has_add_on ? 'success' : 'danger' }}">
                                            {{ $order->has_add_on ? 'Yes' : 'No' }}
                                        </div>
                                    </td>
                                    <td>{{ $order->add_on_count}}</td>
                                    <td>{{ $order->items->sum('qty') }}</td>
                                    {{-- <td>{{ number_format($order->items->sum(fn($i) => $i->qty * $i->price)) }} MMK</td> --}}
                                    <td>{{ number_format($order->total) }} MMK</td>
{{--                                    <td>--}}
{{--                                        @if($order->status ==='pending')--}}
{{--                                        <div class="badge rounded-pill text-success bg-light-warning">--}}
{{--                                            {{ $order->status }}--}}
{{--                                        </div>--}}
{{--                                        @elseif($order->status ==='confirmed')--}}
{{--                                            <div class="badge rounded-pill text-success bg-light-info">--}}
{{--                                                    {{ $order->status }}--}}
{{--                                            </div>--}}
{{--                                        @elseif($order->status ==='preparing')--}}
{{--                                            <div class="badge rounded-pill text-success bg-light-secondary">--}}
{{--                                                {{ $order->status }}--}}
{{--                                            </div>--}}
{{--                                        @elseif($order->status ==='delivered')--}}
{{--                                            <div class="badge rounded-pill text-primary bg-light-primary">--}}
{{--                                                {{ $order->status }}--}}
{{--                                            </div>--}}
{{--                                        @elseif($order->status ==='eating')--}}
{{--                                            <div class="badge rounded-pill text-white bg-primary">--}}
{{--                                                {{ $order->status }}--}}
{{--                                            </div>--}}
{{--                                        @elseif($order->status ==='done')--}}
{{--                                            <div class="badge rounded-pill text-white bg-success">--}}
{{--                                                {{ $order->status }}--}}
{{--                                            </div>--}}
{{--                                        @endif--}}

{{--                                    </td>--}}
                                    <td class="status-cell" data-order-id="{{ $order->id }}">
                                        <span class="status-badge badge rounded-pill w-100
                                            {{ $order->status === 'pending' ? 'bg-light-warning text-success' : '' }}
                                            {{ $order->status === 'confirmed' ? 'bg-light-info text-success' : '' }}
                                            {{ $order->status === 'preparing' ? 'bg-light-secondary text-success' : '' }}
                                            {{ $order->status === 'delivered' ? 'bg-light-primary text-primary' : '' }}
                                            {{ $order->status === 'eating' ? 'bg-primary text-white' : '' }}
                                            {{ $order->status === 'done' ? 'bg-success text-white' : '' }}
                                            {{ $order->status === 'canceled' ? 'bg-danger text-white' : '' }}"
                                        >
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>

                                        <div class="d-flex align-items-center gap-2">
                                            <select class="form-select form-select-sm change-status"
                                                    data-id="{{ $order->id }}"
                                                    style="min-width: 110px">
                                                @foreach(['preparing', 'pending', 'confirmed', 'delivered', 'eating', 'done', 'canceled'] as $status)
                                                    <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>
                                                        {{ ucfirst($status) }}
                                                    </option>
                                                @endforeach
                                            </select>

                      <a href="{{ route('orders.slip', [$order->id, 'print' => 1, 'paper' => '80']) }}" class="btn btn-sm btn-success">Voucher Print</a>
                                            <a href="{{route('admin.orders.show',[$order->id])}}" class="btn btn-sm btn-outline-info"><i class="bx bx-show"></i></a>
                                        </div>

                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
@endsection

@section('custom-js')
    <!-- Flatpickr and Month Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let ordersChart, salesChart;

        function renderCharts(data) {
            if (ordersChart) ordersChart.destroy();
            if (salesChart) salesChart.destroy();

            const ctxOrders = document.getElementById('ordersChart').getContext('2d');
            const ctxSales = document.getElementById('salesChart').getContext('2d');

            // Orders Chart: Modern Donut with center label
            ordersChart = new Chart(ctxOrders, {
                type: 'doughnut',
                data: {
                    labels: ['Orders'],
                    datasets: [{
                        label: 'Orders',
                        data: [data.orders, 1000 - data.orders], // Fake background circle
                        backgroundColor: ['#4e73df', '#e9ecef'],
                        borderWidth: 2,
                        cutout: '75%'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: { enabled: true },
                        legend: { display: false },
                        title: {
                            display: true,
                            text: `Total Orders: ${data.orders}`,
                            color: '#4e73df',
                            font: {
                                size: 16
                            }
                        }
                    }
                }
            });

            // Sales Chart: Horizontal bar with color and value labels
            salesChart = new Chart(ctxSales, {
                type: 'bar',
                data: {
                    labels: ['TakeAway', 'DineIn'],
                    datasets: [{
                        label: 'Sales (MMK)',
                        data: [data.sales_takeaway, data.sales_dinein],
                        backgroundColor: ['#fd7e14', '#20c997'],
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                color: '#6c757d'
                            },
                            grid: {
                                color: '#f1f1f1'
                            }
                        },
                        y: {
                            ticks: {
                                color: '#6c757d',
                                font: {
                                    weight: 'bold'
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => `${ctx.dataset.label}: ${ctx.formattedValue} MMK`
                            }
                        },
                        title: {
                            display: true,
                            text: 'Sales Summary',
                            font: {
                                size: 16
                            }
                        }
                    }
                }
            });
        }

    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rangeSelect = document.getElementById('date-range');
            const customDateRange = document.getElementById('custom-date-range');
            const customMonth = document.getElementById('custom-month');
            const customYear = document.getElementById('custom-year');
            const startInput = document.getElementById('custom-start');
            const endInput = document.getElementById('custom-end');

            // Month picker setup
            flatpickr("#custom-month-value", {
                dateFormat: "Y-m",
                altFormat: "F Y",
                altInput: true,
                maxDate: new Date(),
                plugins: [
                    new monthSelectPlugin({
                        shorthand: true,
                        dateFormat: "Y-m",
                        altFormat: "F Y",
                        theme: "light"
                    })
                ]
            });

            // Generate years for Select2 dropdown
            const yearSelect = document.getElementById('custom-year-select');
            const thisYear = new Date().getFullYear();
            for (let y = thisYear; y >= thisYear - 50; y--) {
                const opt = document.createElement('option');
                opt.value = y;
                opt.text = y;
                yearSelect.appendChild(opt);
            }
            $('#custom-year-select').select2({
                placeholder: "Choose Year",
                width: '100%'
            });

            // Toggle date range blocks
            rangeSelect.addEventListener('change', function () {
                const val = this.value;
                customDateRange.classList.add('d-none');
                customMonth.classList.add('d-none');
                customYear.classList.add('d-none');

                if (val === 'custom') {
                    customDateRange.classList.remove('d-none');
                } else if (val === 'custom-month') {
                    customMonth.classList.remove('d-none');
                } else if (val === 'custom-year') {
                    customYear.classList.remove('d-none');
                } else {
                    fetchStats({ range: val });
                }
            });

            document.getElementById('apply-custom-date').addEventListener('click', () => {
                const start = startInput.value;
                const end = endInput.value;
                if (!start || !end || new Date(end) < new Date(start)) {
                    alert('Invalid date range!');
                    return;
                }
                fetchStats({ range: 'custom', start, end });
            });

            document.getElementById('apply-custom-month').addEventListener('click', () => {
                const month = document.getElementById('custom-month-value').value;
                if (!month) {
                    alert("Please choose a month");
                    return;
                }
                fetchStats({ range: 'custom-month', month });
            });

            document.getElementById('apply-custom-year').addEventListener('click', () => {
                const year = document.getElementById('custom-year-select').value;
                if (!year) {
                    alert("Please choose a year");
                    return;
                }
                fetchStats({ range: 'custom-year', year });
            });

            startInput.addEventListener('change', function () {
                endInput.min = this.value;
                endInput.value = '';
            });

            // Fetch data function
            function fetchStats(params) {
                const query = new URLSearchParams(params).toString();
                fetch(`/admin/ajax-dashboard-stats?${query}`)
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('stats').innerHTML = `
                            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
    <div class="col">
        <div class="card radius-10 bg-gradient-deepblue">
            <div class="card-body">
                <h5 class="mb-0 text-white">${data.orders}</h5>
                <p class="text-white"><i class="bx bx-cart"></i> Orders</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 bg-gradient-orange">
            <div class="card-body">
                <h5 class="mb-0 text-white">${data.sales_takeaway} MMK</h5>
                <p class="text-white"><i class="bx bx-package"></i> Sales (TakeAway)</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 bg-gradient-ibiza">
            <div class="card-body">
                <h5 class="mb-0 text-white">${data.sales_dinein} MMK</h5>
                <p class="text-white"><i class="bx bx-restaurant"></i> Sales (DineIn)</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 bg-gradient-ohhappiness">
            <div class="card-body">
                <h5 class="mb-0 text-white">${data.customers}</h5>
                <p class="text-white"><i class="bx bx-user-circle"></i> Customers</p>
            </div>
        </div>
    </div>
</div>

                        `;
                        renderCharts(data); // ✅ Add this line to update the chart

                    });
            }
            // Reset all filters on initial load
            rangeSelect.value = 'today';
            customDateRange.classList.add('d-none');
            customMonth.classList.add('d-none');
            customYear.classList.add('d-none');

            startInput.value = '';
            endInput.value = '';
            document.getElementById('custom-month-value').value = '';
            $('#custom-year-select').val(null).trigger('change');


            fetchStats({ range: 'today' });


        });
    </script>
                    <script>
                        $(document).ready(function () {
                            $('.change-status').on('change', function () {
                                const orderId = $(this).data('id');
                                const newStatus = $(this).val();

                                $.ajax({
                                    url: `/admin/orders/${orderId}/status`,
                                    type: 'PATCH',
                                    data: {
                                        _token: '{{ csrf_token() }}',
                                        status: newStatus
                                    },
                                    success: function (response) {
                                        toastr.success('Status updated successfully!');

                                        // Update the badge UI
                                        const badge = $(`td.status-cell[data-order-id="${orderId}"] .status-badge`);
                                        badge.removeClass();

                                        let newClass = 'status-badge badge rounded-pill w-100 ';

                                        switch (newStatus) {
                                            case 'pending':
                                                newClass += 'bg-light-warning text-success';
                                                break;
                                            case 'confirmed':
                                                newClass += 'bg-light-info text-success';
                                                break;
                                            case 'preparing':
                                                newClass += 'bg-light-secondary text-success';
                                                break;
                                            case 'delivered':
                                                newClass += 'bg-light-primary text-primary';
                                                break;
                                            case 'eating':
                                                newClass += 'bg-primary text-white';
                                                break;
                                            case 'done':
                                                newClass += 'bg-success text-white';
                                                break;
                                            case 'canceled':
                                                newClass += 'bg-danger text-white';
                                                break;
                                        }

                                        badge.addClass(newClass).text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));
                                    },
                                    error: function (xhr) {
                                        toastr.error('Failed to update status.');
                                    }
                                });
                            });
                        });
                    </script>

                    <script>
                        $(document).ready(function () {
                            const table = $('#orders-table').DataTable({
                                lengthChange: false,
                                ordering: false,
                                pageLength: 10,
                                buttons: [
                                    {
                                        extend: 'copyHtml5',
                                        exportOptions: { columns: [0,1,2,3,4,5,6,7] }
                                    },
                                    {
                                        extend: 'excelHtml5',
                                        exportOptions: { columns: [0,1,2,3,4,5,6,7] }
                                    },
                                    {
                                        extend: 'pdfHtml5',
                                        exportOptions: { columns: [0,1,2,3,4,5,6,7] }
                                    },
                                    {
                                        extend: 'print',
                                        exportOptions: { columns: [0,1,2,3,4,5,6,7] }
                                    }
                                ]
                            });

                            table.buttons().container().appendTo('#orders-table_wrapper .col-md-6:eq(0)');
                        });
                        $(document).ready(function () {
                            $('#filter-status, #filter-order-type').on('change', function () {
                                const status = $('#filter-status').val();
                                const orderType = $('#filter-order-type').val();

                                let query = '';
                                if (status) query += `status=${status}`;
                                if (orderType) query += `${query ? '&' : ''}order_type=${orderType}`;
                                console.log(orderType)

                                const baseUrl = window.location.pathname;
                                window.location.href = query ? `${baseUrl}?${query}` : baseUrl;
                            });
                        });
                    </script>
@endsection



