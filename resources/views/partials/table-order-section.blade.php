<section id="order-table-{{ $table->id }}"
         class="order-section"
         data-parent-order-id="{{ $mainOrder?->id }}">

    @if($mainOrder)
        <div class="bg-white rounded-xl p-4 shadow space-y-4 overflow-y-auto max-h-[30vh] no-scrollbar">
            <div class="bg-white rounded-lg p-4 shadow-inner space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="font-semibold text-lg">
                        🎟️ Order No: <span class="font-bold">{{ $mainOrder->order_no }}</span>
                    </h3>
                    <div class="text-sm px-3 py-1 rounded-full font-semibold bg-green-600 text-white">
                        {{ ucfirst($mainOrder->status) }}
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach($mainOrder->items as $item)
                        <div class="flex justify-between">
                            <span>{{ $item->product->name }}</span>
                            <strong>{{ $item->qty }}</strong>
                        </div>
                    @endforeach
                </div>

                @php
                    $total = collect($mainOrder->items)->sum(fn($item) => $item->price * $item->qty);
                @endphp

                <div class="flex justify-between text-sm text-gray-500 border-t pt-2">
                    <span>{{ $mainOrder->created_at->format('d/m/Y \a\t h:i A') }}</span>
                    <span class="font-bold text-black">{{ number_format($total) }} MMK</span>
                </div>
            </div>

            @foreach($addOnOrders as $addOn)
                <div class="bg-gray-50 rounded-lg p-4 shadow-inner space-y-2">
                    <div class="flex justify-between items-center">
                        <h4 class="text-sm font-semibold text-gray-700">
                            ➕ Add-On Order: <span class="font-bold">{{ $addOn->order_no }}</span>
                        </h4>
                        <span class="text-xs px-3 py-1 rounded-full bg-blue-600 text-white">
                            {{ ucfirst($addOn->status) }}
                        </span>
                    </div>

                    <div class="space-y-2 text-sm">
                        @foreach($addOn->items as $item)
                            <div class="flex justify-between">
                                <span>{{ $item->product->name }}</span>
                                <strong>{{ $item->qty }}</strong>
                            </div>
                        @endforeach
                    </div>

                    @php
                        $addOnTotal = collect($addOn->items)->sum(fn($item) => $item->price * $item->qty);
                    @endphp

                    <div class="flex justify-between text-xs text-gray-500 border-t pt-2">
                        <span>{{ $addOn->created_at->format('d/m/Y \a\t h:i A') }}</span>
                        <span class="font-bold text-black">{{ number_format($addOnTotal) }} MMK</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-right mt-4" id="add-on-{{ $table->id }}">
            <button onclick="showAddOn('{{ $table->name }}', '{{ $table->id }}')" class="bg-green-700 text-white font-bold px-6 py-2 rounded-full hover:bg-green-800">
                Add-On Order
            </button>
        </div>
    @else
        <div id="empty-order-{{ $table->id }}" class="text-center space-y-4 py-12">
            <img src="{{ asset('/assets/images/orders/no-order.jpg') }}" class="w-64 mx-auto" />
            <p>No active orders at this table.</p>
            <button onclick="startOrder('{{ $table->name }}', '{{ $table->id }}')" class="bg-green-600 text-white px-6 py-2 rounded-full hover:bg-green-700">
                Start Order
            </button>
        </div>
    @endif
</section>
