{{-- @php


  $paper = request('paper', '80');            // '80' or '58'
  $autoPrint = request('print') ? true : false;

  $W = $paper === '58' ? '58mm' : '80mm';
  $fs = $paper === '58' ? '11px' : '12px';    // base font
  $fsHead = $paper === '58' ? '12px' : '13px';
  $qtyW   = $paper === '58' ? '3ch'  : '4ch';
  $priceW = $paper === '58' ? '7ch'  : '8ch';
  $lineW  = $paper === '58' ? '8ch'  : '9ch';
@endphp
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Bill Slip - {{ $combinedOrderNos }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{
      --w: {{ $W }};
      --fs: {{ $fs }};
      --fs-head: {{ $fsHead }};
      --mono: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
      --sans: -apple-system, BlinkMacSystemFont, "Inter", Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol";
      --ink: #000;
      --muted: #555;
    }
    *{ box-sizing:border-box; }
    html, body{ margin:0; padding:0; background:#fff; color:var(--ink); }
    body{ font-family:var(--sans); font-size:var(--fs); line-height:1.25; }
    .wrap{ width:var(--w); margin:0 auto; padding:6px 6px 10px; }

    .center{ text-align:center; }
    .right{ text-align:right; }
    .muted{ color:var(--muted); }
    .bold{ font-weight:700; }
    .title{ font-weight:800; letter-spacing:.3px; margin-top:2px; font-size:var(--fs-head); }

    .logo{ text-align:center; margin-bottom:4px; }
    .logo img{ height:40px; width:auto; }

    .rule{ height:1px; margin:6px 0; background:repeating-linear-gradient(
      to right, var(--ink), var(--ink) 4px, transparent 4px, transparent 8px
    ); opacity:.55; }

    .meta{
      display:flex; justify-content:space-between; gap:8px;
    }
    .meta .left{ max-width: calc(var(--w) - 70px); }
    .meta strong{ font-weight:700; }

    table{ width:100%; border-collapse:collapse; }
    thead th{
      text-transform:uppercase; font-size:calc(var(--fs) - 1px);
      padding:2px 0 4px; border-bottom:1px dashed #888;
    }
    th,td{ vertical-align:top; }
    .itm-name{ font-weight:700; }
    .itm-comment{ font-style:italic; color:var(--muted); font-size:calc(var(--fs) - 1px); }

    /* fixed column widths */
    .qty{ width: {{ $qtyW }}; text-align:right; font-family:var(--mono); }
    .price{ width: {{ $priceW }}; text-align:right; font-family:var(--mono); }
    .line{ width: {{ $lineW }}; text-align:right; font-family:var(--mono); }

    .totals td{ padding:2px 0; }
    .grand{ border-top:1px dashed #888; padding-top:4px; font-weight:800; }

    /* chips are outlined only (ink-friendly) */
    .chips{ display:flex; flex-wrap:wrap; gap:4px; margin-top:4px; }
    .chip{ border:1px dashed #777; padding:1px 5px; border-radius:9999px; font-size:calc(var(--fs) - 2px); color:#333; }

    .footer{ margin-top:8px; text-align:center; color:var(--muted); }
    .cut{
      margin-top:8px; height:1px;
      background:repeating-linear-gradient(to right, var(--ink), var(--ink) 6px, transparent 6px, transparent 12px);
      opacity:.55;
    }

    @media print{
      @page { size: var(--w) auto; margin: 0; }
      body{ -webkit-print-color-adjust:exact; print-color-adjust:exact; }
      .no-print{ display:none !important; }
      .wrap{ padding:6px 6px 8px; }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="logo">
      <img src="{{ asset('assets/images/logo/logo.png') }}" alt="logo">
    </div>
    <div class="center bold">Laravel</div>
    <div class="center title">BILL SLIP</div>

    <div class="rule"></div>

    <div class="meta">
      <div class="left">
        <div><strong>Location</strong>: {{ $placeLabel }}</div>
        <div style="word-break: break-word;">
          <strong>Order(s)</strong>: {{ $combinedOrderNos }}
        </div>
        <div class="muted">
          {{ $root->order_type === 'dine_in' ? 'Dine-in' : 'Takeaway' }}
          @if($root->order_type === 'dine_in' && $root->table) • Table: {{ $root->table->name }} @endif
        </div>
      </div>
      <div class="right">
        <div><strong>Date</strong></div>
        <div>{{ $now->format('Y-m-d') }}</div>
        <div>{{ $now->format('H:i') }}</div>
      </div>
    </div>

    <div class="rule"></div>

    <table>
      <thead>
        <tr>
          <th style="text-align:left;">ITEM</th>
          <th class="qty">QTY</th>
          <th class="price">PRICE</th>
          <th class="line">LINE</th>
        </tr>
      </thead>
      <tbody>
        @foreach($items as $it)
          <tr>
            <td>
              <div class="itm-name">{{ $it['name'] }}</div>
              @if(!empty($it['comment']))
                <div class="itm-comment">* {{ $it['comment'] }}</div>
              @endif
            </td>
            <td class="qty">{{ $it['qty'] }}</td>
            <td class="price">{{ number_format($it['price']) }}</td>
            <td class="line">{{ number_format($it['price'] * $it['qty']) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="rule"></div>

    <table class="totals">
      <tr>
        <td>Subtotal</td>
        <td class="line">{{ number_format($subtotal) }}&nbsp;MMK</td>
      </tr>
      <tr>
        <td>
          Tax {{ rtrim(rtrim(number_format($taxPercent, 2), '0'), '.') }}%
          @if($taxSnapshot) <span class="muted" style="font-size:10px;">({{ $taxSnapshot }})</span> @endif
        </td>
        <td class="line">{{ number_format($taxAmount) }}&nbsp;MMK</td>
      </tr>
      <tr class="grand">
        <td>Total</td>
        <td class="line">{{ number_format($total) }}&nbsp;MMK</td>
      </tr>
    </table>

    @if(!empty($taxSnapshot))
      <div class="chips">
        @foreach(explode('+', str_replace(' ', '', $taxSnapshot)) as $chip)
          <span class="chip">{{ trim($chip) }}</span>
        @endforeach
      </div>
    @endif

    <div class="cut"></div>

    <div class="footer">
      Thanks for dining with us!<br>
      <span style="font-size:10px;">Keep this slip for your reference.</span>
    </div>

    <div class="no-print center" style="margin-top:8px;">
      <button onclick="window.print()" style="padding:6px 10px;border:1px solid #111;border-radius:4px;background:#fff;cursor:pointer;">
        Print
      </button>
    </div>
  </div>

  @if($autoPrint)
  <script>window.addEventListener('load',()=>window.print())</script>
  @endif
</body>
</html> --}}
@php
  $W = ($paper ?? '80') === '58' ? '58mm' : '80mm';
  $fs = ($paper ?? '80') === '58' ? '11px' : '12px';
  $fsHead = ($paper ?? '80') === '58' ? '12px' : '13px';
  $qtyW   = ($paper ?? '80') === '58' ? '3ch'  : '4ch';
  $priceW = ($paper ?? '80') === '58' ? '7ch'  : '8ch';
  $lineW  = ($paper ?? '80') === '58' ? '8ch'  : '9ch';
@endphp
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Bill Slip - {{ $combinedOrderNos }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{
      --w: {{ $W }};
      --fs: {{ $fs }};
      --fs-head: {{ $fsHead }};
      --mono: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
      --sans: -apple-system, BlinkMacSystemFont, "Inter", Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol";
      --ink: #000; --muted: #555;
    }
    *{ box-sizing:border-box }
    html,body{ margin:0; background:#fff; color:var(--ink) }
    body{ font-family:var(--sans); font-size:var(--fs); line-height:1.25 }
    .wrap{ width:var(--w); margin:0 auto; padding:6px 6px 10px }

    .center{text-align:center}.right{text-align:right}.muted{color:var(--muted)}.bold{font-weight:700}
    .title{ font-weight:800; letter-spacing:.3px; margin-top:2px; font-size:var(--fs-head) }
    .logo{ text-align:center; margin-bottom:4px } .logo img{ height:40px; width:auto }

    .rule{ height:1px; margin:6px 0; background:repeating-linear-gradient(
      to right, var(--ink), var(--ink) 4px, transparent 4px, transparent 8px); opacity:.55 }
    .meta{ display:flex; justify-content:space-between; gap:8px }
    .meta .left{ max-width: calc(var(--w) - 70px) }
    .meta strong{ font-weight:700 }

    table{ width:100%; border-collapse:collapse }
    thead th{ text-transform:uppercase; font-size:calc(var(--fs) - 1px);
      padding:2px 0 4px; border-bottom:1px dashed #888 }
    th,td{ vertical-align:top }
    .itm-name{ font-weight:700 }
    .itm-comment{ font-style:italic; color:var(--muted); font-size:calc(var(--fs) - 1px) }

    .qty{ width: {{ $qtyW }}; text-align:right; font-family:var(--mono) }
    .price{ width: {{ $priceW }}; text-align:right; font-family:var(--mono) }
    .line{ width: {{ $lineW }}; text-align:right; font-family:var(--mono) }

    .totals td{ padding:2px 0 } .grand{ border-top:1px dashed #888; padding-top:4px; font-weight:800 }
    .chips{ display:flex; flex-wrap:wrap; gap:4px; margin-top:4px }
    .chip{ border:1px dashed #777; padding:1px 5px; border-radius:9999px; font-size:calc(var(--fs) - 2px); color:#333 }

    .footer{ margin-top:8px; text-align:center; color:var(--muted) }
    .cut{ margin-top:8px; height:1px; background:repeating-linear-gradient(
      to right, var(--ink), var(--ink) 6px, transparent 6px, transparent 12px); opacity:.55 }

    .tag{ display:inline-block; padding:1px 6px; border:1px solid #111; border-radius:9999px; margin-left:4px }

    @media print{ @page { size: var(--w) auto; margin: 0 } .no-print{ display:none !important } }
  </style>
</head>
<body>
  <div class="wrap">
    {{-- Header --}}
    <div class="logo">
      <img src="{{ asset('assets/images/logo/logo.png') }}" alt="logo">
    </div>
    <div class="center bold">{{ $brand['name'] ?? 'Restaurant' }}</div>
    <div class="center title">BILL SLIP</div>
    @if(($brand['addr1'] ?? '') || ($brand['phone'] ?? ''))
      <div class="center muted" style="font-size:10px">
        {{ $brand['addr1'] ?? '' }} {{ $brand['addr2'] ?? '' }} {{ $brand['phone'] ? '• '.$brand['phone'] : '' }}
      </div>
    @endif

    <div class="rule"></div>

    {{-- Meta --}}
    <div class="meta">
      <div class="left">
        <div><strong>Location</strong>: {{ $placeLabel }}</div>
        <div style="word-break:break-word"><strong>Order(s)</strong>: {{ $combinedOrderNos }}</div>
        <div class="muted">
          {{ $root->order_type === 'dine_in' ? 'Dine-in' : 'Takeaway' }}
          @if($root->order_type === 'dine_in' && $root->table) • Table: {{ $root->table->name }} @endif
          @if(!empty($root->parent_order_id))
            <span class="tag">Add-on of #{{ optional($root->parent)->order_no ?? $root->parent_order_id }}</span>
          @else
            <span class="tag">Main</span>
          @endif
        </div>
        @if(!empty($printedBy))
          <div class="muted" style="margin-top:2px">Printed by: {{ $printedBy }}</div>
        @endif
      </div>
      <div class="right">
        <div><strong>Date</strong></div>
        <div>{{ $now->format('Y-m-d') }}</div>
        <div>{{ $now->format('H:i') }}</div>
      </div>
    </div>

    <div class="rule"></div>

    {{-- Items (combined) --}}
    @if($mode === 'combined')
      <table>
        <thead>
        <tr>
          <th style="text-align:left">ITEM</th>
          <th class="qty">QTY</th>
          <th class="price">PRICE</th>
          <th class="line">LINE</th>
        </tr>
        </thead>
        <tbody>
        @foreach($items as $it)
          <tr>
            <td>
              <div class="itm-name">{{ $it['name'] }}</div>
              @if(!empty($it['comment']))
                <div class="itm-comment">* {{ $it['comment'] }}</div>
              @endif
            </td>
            <td class="qty">{{ $it['qty'] }}</td>
            <td class="price">{{ number_format($it['price']) }}</td>
            <td class="line">{{ number_format($it['price'] * $it['qty']) }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    @endif

    {{-- Detailed sections --}}
    @if($mode === 'detailed')
      @foreach($sections as $s)
        <div style="margin-bottom:6px">
          <div class="bold">{{ $s['title'] }}
            <span class="tag">{{ ucfirst($s['status']) }}</span>
            <span class="muted" style="margin-left:6px">{{ \Carbon\Carbon::parse($s['time'])->format('H:i') }}</span>
          </div>
          <table>
            <thead>
              <tr>
                <th style="text-align:left">ITEM</th>
                <th class="qty">QTY</th>
                <th class="price">PRICE</th>
                <th class="line">LINE</th>
              </tr>
            </thead>
            <tbody>
              @foreach($s['items'] as $it)
                <tr>
                  <td>
                    <div class="itm-name">{{ $it['name'] }}</div>
                    @if(!empty($it['comment']))
                      <div class="itm-comment">* {{ $it['comment'] }}</div>
                    @endif
                  </td>
                  <td class="qty">{{ $it['qty'] }}</td>
                  <td class="price">{{ number_format($it['price']) }}</td>
                  <td class="line">{{ number_format($it['price'] * $it['qty']) }}</td>
                </tr>
              @endforeach
              <tr>
                <td colspan="3" class="right muted">Section subtotal</td>
                <td class="line">{{ number_format($s['sum']) }}</td>
              </tr>
            </tbody>
          </table>
          <div class="rule"></div>
        </div>
      @endforeach
    @endif

    {{-- Totals --}}
    <table class="totals">
      <tr>
        <td>Subtotal</td>
        <td class="line">{{ number_format($subtotal) }}&nbsp;MMK</td>
      </tr>

      @if(($discountAmount ?? 0) > 0)
        <tr>
          <td>
            Discount
            @if($discountType === 'percent') ({{ rtrim(rtrim(number_format($discountValue,2),'0'),'.') }}%) @endif
          </td>
          <td class="line">-{{ number_format($discountAmount) }}&nbsp;MMK</td>
        </tr>
        <tr>
          <td>Net Subtotal</td>
          <td class="line">{{ number_format($netSubtotal) }}&nbsp;MMK</td>
        </tr>
      @endif

      <tr>
        <td>
          Tax {{ rtrim(rtrim(number_format($taxPercent, 2), '0'), '.') }}%
          @if($taxSnapshot) <span class="muted" style="font-size:10px">({{ $taxSnapshot }})</span> @endif
        </td>
        <td class="line">{{ number_format($taxAmount) }}&nbsp;MMK</td>
      </tr>

      <tr class="grand">
        <td>Total</td>
        <td class="line">{{ number_format($total) }}&nbsp;MMK</td>
      </tr>

      @if(($paid ?? 0) > 0)
        <tr>
          <td>Paid{{ $method ? " ({$method})" : '' }}</td>
          <td class="line">{{ number_format($paid) }}&nbsp;MMK</td>
        </tr>
        <tr>
          <td>Change</td>
          <td class="line">{{ number_format($change) }}&nbsp;MMK</td>
        </tr>
      @endif
    </table>

    {{-- Tax chips --}}
    @if(!empty($taxSnapshot))
      <div class="chips">
        @foreach(explode('+', str_replace(' ', '', $taxSnapshot)) as $chip)
          <span class="chip">{{ trim($chip) }}</span>
        @endforeach
      </div>
    @endif

    <div class="rule"></div>

    {{-- QR + footer --}}
    <div class="center" style="margin:6px 0">
      {!! $qrSvg !!}
    </div>

    <div class="footer">
      Thanks for dining with us!<br>
      <span style="font-size:10px;">This slip includes Main + Add-on orders.</span>
    </div>

    <div class="cut"></div>

    <div class="no-print center" style="margin-top:8px;">
      <button onclick="window.print()" style="padding:6px 10px;border:1px solid #111;border-radius:4px;background:#fff;cursor:pointer;">
        Print
      </button>
    </div>
  </div>

  @if($autoPrint)
  <script>addEventListener('load',()=>print())</script>
  @endif
</body>
</html>
