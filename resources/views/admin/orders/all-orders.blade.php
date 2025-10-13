@extends('admin.layouts.app')

@section('custom-css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Ensure dynamically added selects match layout */
        select.change-status { width: 100% !important; min-width: 140px; }
        td select.form-select { max-width: 100%; }
        .status-badge { white-space: nowrap; }
    </style>
@endsection

@section('content')
    @include('admin.components.left_sidebar')
    @include('admin.components.header')
    @include('admin.components.right_sidebar')

    <div class="page-wrapper">
        <div class="page-content">
            <h3>Orders and Summary</h3>

            <div class="card radius-10">
                <div class="card-body">

                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="mb-0">Orders Summary</h5>

                        <div class="d-flex flex-wrap gap-2 mb-3 align-items-end">
                            <div>
                                <label>Status</label>
                                <select id="filter-status" class="form-select form-select-sm" style="width: 200px;">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="preparing">Preparing</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="eating">Eating</option>
                                    <option value="done">Done</option>
                                    <option value="canceled">Canceled</option>
                                </select>
                            </div>

                            <div>
                                <label>Date</label>
                                <input type="text" id="filter-date" class="form-control form-control-sm" placeholder="Pick a day">
                            </div>

                            <div>
                                <label>Week</label>
                                <input type="text" id="filter-week" class="form-control form-control-sm" placeholder="Select week">
                            </div>

                            <div>
                                <label>Year</label>
                                <input type="text" id="filter-year" class="form-control form-control-sm" placeholder="Pick a year">
                            </div>

                            <div>
                                <label>Custom Range</label>
                                <input type="text" id="filter-range" class="form-control form-control-sm" placeholder="Custom range">
                            </div>

                            <button id="apply-filters" class="btn btn-sm btn-primary">Apply Filters</button>
                            <button id="reset-filters" class="btn btn-sm btn-secondary">Reset</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="orders-table" class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order</th>
                                    <th>Order Type</th>
                                    <th>Table</th>
                                    <th>Customer</th>
                                    <th>Created At</th>
                                    <th>Has Add-on</th>
                                    <th>Add-On Count</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="orders-table-body">
                                @include('admin.orders.partials.orders_table_rows', ['orders' => $orders])
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>

    {{-- Confirm modal for irreversible transitions + cascade --}}
    <div class="modal fade" id="confirmStatusModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header"><h5 class="modal-title">Confirm status change</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p id="confirmText" class="mb-2"></p>
            <div id="cascadeWrap" class="form-check d-none">
              <input class="form-check-input" type="checkbox" id="cascadeCheckbox">
              <label for="cascadeCheckbox" class="form-check-label">
                Also apply to all open add-on orders of this main order
              </label>
              <div class="form-text" id="cascadeCountHelp"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button id="btnConfirmNo" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button id="btnConfirmYes" type="button" class="btn btn-primary">Yes, change</button>
          </div>
        </div>
      </div>
    </div>
@endsection

@section('custom-js')
    {{-- deps --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // pickers
        flatpickr("#filter-date", { dateFormat: "Y-m-d" });
        flatpickr("#filter-week", { mode: "range", dateFormat: "Y-m-d", weekNumbers: true });
        flatpickr("#filter-year", {
            plugins: [new monthSelectPlugin({ shorthand: true, dateFormat: "Y", theme: "light" })]
        });
        flatpickr("#filter-range", { mode: "range", dateFormat: "Y-m-d" });
    </script>

    <script>
      // ===== Status UX: mapping & guards =====
      const STATUS_BADGE = {
        pending:   'bg-light-warning text-success',
        confirmed: 'bg-light-info text-success',
        preparing: 'bg-light-secondary text-success',
        delivered: 'bg-light-primary text-primary',
        eating:    'bg-primary text-white',
        done:      'bg-success text-white',
        canceled:  'bg-danger text-white',
        _default:  'bg-secondary text-white',
      };

      // Simple state machine for allowed transitions
      const FSM = {
        pending:   ['confirmed','canceled'],
        confirmed: ['preparing','canceled'],
        preparing: ['delivered','canceled'],
        delivered: ['eating','done'],
        eating:    ['done'],
        done:      [],
        canceled:  [],
      };

      function allowedFrom(status){ return FSM[status] || []; }
      function isTerminal(status){ return status === 'done' || status === 'canceled'; }

      // Disable invalid options per current status, and disable whole select if terminal
      function applyStatusGuards(scope=document){
        scope.querySelectorAll('#orders-table .change-status').forEach(sel=>{
          const current = sel.value;
          const allowed = new Set([current, ...allowedFrom(current)]);
          sel.querySelectorAll('option').forEach(opt=>{
            opt.disabled = !allowed.has(opt.value);
          });
          sel.disabled = isTerminal(current); // lock if done/canceled
        });
      }
    </script>

    <script>
        // =========== DataTable + Filters ===========
        let dataTableInstance;

        function initDataTable() {
            dataTableInstance = $('#orders-table').DataTable({
                destroy: true,
                lengthChange: true,
                ordering: false,
                pageLength: 10,
                buttons: [
                    { extend: 'copyHtml5', exportOptions: { columns: [0,1,2,3,4,5,6,7] } },
                    { extend: 'excelHtml5', exportOptions: { columns: [0,1,2,3,4,5,6,7] } },
                    { extend: 'pdfHtml5',   exportOptions: { columns: [0,1,2,3,4,5,6,7] } },
                    { extend: 'print',      exportOptions: { columns: [0,1,2,3,4,5,6,7] } }
                ]
            });
            dataTableInstance.buttons().container().appendTo('#orders-table_wrapper .col-md-6:eq(0)');
            applyStatusGuards(document); // enforce guards after DT init
        }

        function clearOtherFilters(except) {
            const fields = ['date', 'week', 'year', 'range'];
            fields.forEach(field => {
                if (field !== except) { $(`#filter-${field}`).val(''); }
            });
        }

        function fetchAndRender(payload = {}) {
            $.ajax({
                url: "{{ route('admin.orders.filter') }}",
                type: "GET",
                data: payload,
                beforeSend: function () {
                    if (dataTableInstance) dataTableInstance.clear().destroy();
                    $('#orders-table-body').html('<tr><td colspan="11" class="text-center">Loading…</td></tr>');
                },
                success: function (res) {
                    $('#orders-table-body').html(res.html);
                    initDataTable(); // delegated handlers + guards reapply
                },
                error: function () {
                    toastr.error('Something went wrong while fetching orders.');
                }
            });
        }

        $(document).ready(function () {
            initDataTable();

            // Mutually exclusive date filters
            $('#filter-date').on('change', function(){ clearOtherFilters('date'); });
            $('#filter-week').on('change', function(){ clearOtherFilters('week'); });
            $('#filter-year').on('change', function(){ clearOtherFilters('year'); });
            $('#filter-range').on('change', function(){ clearOtherFilters('range'); });

            // Apply filters
            $('#apply-filters').on('click', function () {
                const payload = {
                    status: $('#filter-status').val(),
                    date:   $('#filter-date').val(),
                    week:   $('#filter-week').val(),
                    year:   $('#filter-year').val(),
                    range:  $('#filter-range').val()
                };
                fetchAndRender(payload);
            });

            // Reset filters + reload all
            $('#reset-filters').on('click', function () {
                $('#filter-status, #filter-date, #filter-week, #filter-year, #filter-range').val('');
                fetchAndRender({});
            });
        });
    </script>

    <script>
        // =========== Delegated status change with confirm + cascade & solid UX ===========
        let pendingChange = null; // {selectEl, orderId, prev, next, kind, isMain, addonRows[]}

        // Remember previous selection
        $(document).on('focus', '#orders-table .change-status', function(){
            $(this).data('prev', $(this).val());
        });

        // Intercept change: enforce FSM, confirm irreversible, optionally cascade
        $(document).on('change', '#orders-table .change-status', function(){
            const $sel  = $(this);
            const prev  = $sel.data('prev');
            const next  = $sel.val();
            const row   = $sel.closest('tr')[0];
            const id    = $sel.data('id');
            const kind  = row.getAttribute('data-kind'); // main|addon
            const isMain= kind === 'main';

            // guard transitions
            const allowed = new Set([prev, ... (window.allowedFrom ? allowedFrom(prev) : [])]);
            if (!allowed.has(next)) {
                $sel.val(prev);
                toastr.warning('That transition is not allowed from '+prev+'.');
                return;
            }

            // terminal confirmation
            const terminal = (next === 'done' || next === 'canceled');
            if (terminal) {
                const $siblings = isMain
                    ? $(`tr[data-parent-id="${id}"]`).filter(function(){
                        const s = $(this).find('.change-status').val();
                        return !(s === 'done' || s === 'canceled'); // open add-ons only
                      })
                    : $();

                pendingChange = {
                    selectEl: $sel, orderId: id, prev, next,
                    kind, isMain, addonRows: $siblings.toArray()
                };

                // Fill modal
                $('#confirmText').text(
                    `Change status to "${next.toUpperCase()}"? This cannot be changed back.`
                );
                if (isMain) {
                    const n = pendingChange.addonRows.length;
                    if (n > 0) {
                        $('#cascadeWrap').removeClass('d-none');
                        $('#cascadeCheckbox').prop('checked', true);
                        $('#cascadeCountHelp').text(`${n} open add-on order(s) found.`);
                    } else {
                        $('#cascadeWrap').addClass('d-none');
                        $('#cascadeCheckbox').prop('checked', false);
                        $('#cascadeCountHelp').text('');
                    }
                } else {
                    $('#cascadeWrap').addClass('d-none');
                    $('#cascadeCheckbox').prop('checked', false);
                    $('#cascadeCountHelp').text('');
                }

                const modal = new bootstrap.Modal('#confirmStatusModal');
                modal.show();
                return;
            }

            // non-terminal: apply immediately
            performStatusUpdate($sel, id, next, prev);
        });

        // Modal confirm buttons
        $('#btnConfirmYes').on('click', async function(){
            const modalEl = document.getElementById('confirmStatusModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();

            if (!pendingChange) return;
            const { selectEl, orderId, next, prev, isMain, addonRows } = pendingChange;
            const $sel = selectEl;

            // Update main/addon first
            const ok1 = await performStatusUpdate($sel, orderId, next, prev);
            if (!ok1) { pendingChange = null; return; }

            // Cascade if requested
            const doCascade = $('#cascadeCheckbox').is(':checked') && isMain && addonRows.length;
            if (doCascade) {
                for (const row of addonRows) {
                    const $s2 = $(row).find('.change-status');
                    const cur = $s2.val();
                    if (!(cur === 'done' || cur === 'canceled')) {
                        await performStatusUpdate($s2, $s2.data('id'), next, cur, true);
                    }
                }
                toastr.info('Applied to open add-on orders.');
            }
            pendingChange = null;
        });

        $('#btnConfirmNo').on('click', function(){
            if (!pendingChange) return;
            $(pendingChange.selectEl).val(pendingChange.prev);
            pendingChange = null;
        });

        // Core updater (returns Promise<boolean>)
        function performStatusUpdate($select, orderId, next, prev, silent=false){
            $select.prop('disabled', true);
            return new Promise(resolve=>{
                $.ajax({
                    url: `/admin/orders/${orderId}/status`,
                    type: 'PATCH',
                    data: { _token: '{{ csrf_token() }}', status: next },
                    success: function(){
                        if (!silent) toastr.success('Status updated.');
                        // Update badge
                        const $badge = $(`.status-badge[data-order-id="${orderId}"]`);
                        const cls = STATUS_BADGE[next] || STATUS_BADGE._default;
                        $badge.attr('class', `status-badge badge w-100 rounded-pill ${cls}`)
                              .text(next.charAt(0).toUpperCase()+next.slice(1));

                        // Reapply guards to this row
                        applyStatusGuards($select.closest('tr')[0]);
                        resolve(true);
                    },
                    error: function(){
                        if (!silent) toastr.error('Failed to update status.');
                        $select.val(prev);
                        resolve(false);
                    },
                    complete: function(){ $select.prop('disabled', false); }
                });
            });
        }
    </script>
@endsection
