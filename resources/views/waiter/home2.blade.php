<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <title>Restaurant Orders</title>
    <script src="https://kit.fontawesome.com/34ce5b6af8.js" crossorigin="anonymous"></script>
    <link rel="icon" href="{{asset('assets/images/logo/logo.png')}}" type="image/png" />
    <audio id="notiSound" src="{{ asset('sounds/new_order_receive.wav') }}" preload="auto"  allow="autoplay"></audio>
{{--    <audio id="notiSound2" src="{{ asset('sounds/order-update.wav') }}" preload="auto"></audio>--}}


    <style>
        html,
        body {
            height: 100%;
            overflow: hidden;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .tabs.active {
            cursor: grabbing;
        }
        body {
            font-family: "Inter", sans-serif;
        }
    </style>
</head>
<body class="pt-16 bg-gray-100 text-gray-800">
<nav class="fixed top-0 left-0 right-0 h-14 bg-white shadow flex items-center justify-between px-4 border-b border-green-200 z-50">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 bg-gray-100 rounded-full overflow-hidden">
            <img src="{{asset('assets/images/logo/logo.png')}}" class="h-full w-full object-cover" />
        </div>
        <span class="text-xl font-extrabold text-green-700">Hello! {{Auth::user()->name}}</span>
    </div>
    <div class="flex gap-6">
        <a
            href="{{route('waiter.home')}}"
            class="text-green-700 hover:text-green-600 flex items-center gap-1"
        >
            <svg
                class="h-6 w-6 stroke-current"
                fill="none"
                stroke-width="2"
                viewBox="0 0 24 24"
            >
                <path
                    d="M3 10.5L12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10.5Z"
                />
            </svg>
            <span class="hidden sm:inline">Home</span>
        </a>
        <form method="POST" action="{{ route('waiter.logout') }}">
            @csrf
            <button type="submit" class="hover:text-red-600 flex items-center gap-1 text-gray-800">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6 stroke-current"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M18 15l3-3m0 0l-3-3m3 3H9"
                    />
                </svg>
                <span class="hidden sm:inline">Logout</span>
            </button>
        </form>

    </div>
</nav>

<div class="flex h-[calc(100vh-3.5rem)]">
    <aside class="w-18 md:w-32 lg:w-48 xl:w-64 bg-white p-4 rounded-xl overflow-y-auto">
        <h2 class="font-bold text-lg text-green-700 mb-4">Tables</h2>

        <ul class="space-y-2">
            @foreach($table as $t)
                @php
                   $latestOrder = optional($orders->get($t->id))->first();
                    $status = $latestOrder->status ?? null;
                    $isDelivered = $status === 'delivered';
                    $isCancelled = $status === 'cancel';
                    $isDone =$status ==="done";
                @endphp

                <li
                    onclick="toggleTableActive(this, 'Table {{ $t->id }}', '{{ $t->id}}','{{ $t->name}}')" data-name="{{ $t->name }}"
                    class="table-item flex justify-between items-center p-2 rounded hover:bg-green-50 text-green-700 font-semibold cursor-pointertable-item flex justify-between items-center p-2 rounded hover:bg-green-50 text-green-700 font-semibold cursor-pointer"

                >
                    {{ $t->name }}

                    <span>
                        @if($isDelivered)
                            <i class="fa-solid fa-check-circle fa-lg text-green-500"></i>
                        @elseif(!$isCancelled && $status && !$isDone)
                            <i class="fa-solid fa-clock fa-lg text-gray-400"></i>
                        @endif
                        {{-- No icon if cancelled or no order --}}
            </span>
                </li>
            @endforeach
        </ul>

    </aside>

    <main id="mainContent" class="flex-1 p-6 overflow-hidden space-y-6 relative no-scrollbar">

        <h1 class="text-2xl font-bold text-green-700 mb-6">
            Orders for <span id="activeTableHeading"></span>
        </h1>


        @foreach($table as $t)
            @php
                $rawOrders = $orders->get($t->id); // grouped orders per table
                $mainOrder = null;
                $addOnOrders = [];

                if ($rawOrders instanceof \Illuminate\Support\Collection) {
                    foreach ($rawOrders as $order) {
                        $normalizedStatus = strtolower(trim($order->status));
                        if (!in_array($normalizedStatus, ['done', 'cancel'])) {
                            if (!$order->parent_order_id) {
                                $mainOrder = $order;
                            } else {
                                $addOnOrders[] = $order;
                            }
                        }
                    }
                }
            @endphp
            <section id="order-table-{{ $t->id }}"
                     class="order-section hidden"
                     data-parent-order-id="{{ $mainOrder?->id }}">




            @if($mainOrder)
                    <!-- 🌟 MAIN ORDER -->
                    <div class="bg-white rounded-xl p-4 shadow space-y-4 overflow-y-auto max-h-[30vh] no-scrollbar">
                        <!-- 🌟 Main Order -->
                        <div class=" bg-white rounded-lg p-4 shadow-inner space-y-4">
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

                        <!-- ➕ Add-On Orders -->
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


                    <!-- 🔘 Add-On Button -->
                    <div class="text-right mt-4" id="add-on-{{ $t->id }}">
                        <button onclick="showAddOn('{{ $t->name }}', '{{ $t->id }}')" class="bg-green-700 text-white font-bold px-6 py-2 rounded-full hover:bg-green-800">
                            Add-On Order
                        </button>
                    </div>
                @else
                    <!-- ❌ No active order -->
                    <div id="empty-order-{{ $t->id }}" class="text-center space-y-4 py-12">
                        <img src="{{ asset('/assets/images/orders/no-order.jpg') }}" class="w-64 mx-auto" />
                        <p>No active orders at this table.</p>
                        <button onclick="startOrder('{{ $t->name }}', '{{ $t->id }}')" class="bg-green-600 text-white px-6 py-2 rounded-full hover:bg-green-700">
                            Start Order
                        </button>
                    </div>
                @endif
            </section>

        @endforeach

        <section id="addOnMenuSection" class="hidden">
            <h2 class="font-bold text-green-600 text-xl mb-4">Menus for <span id="menuTableName"></span></h2>

            <!-- Category Tabs -->
            <div id="menuTabs" class="overflow-x-auto whitespace-nowrap pb-2 space-x-2 flex no-scrollbar">
                @foreach($categories as $category)
                    <button
                        class="menu-tab px-4 py-1 rounded-full font-semibold
                        {{ $loop->first ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700' }}"
                        onclick="fetchMenu('{{ $category->name }}')"
                        data-category="{{ $category->name }}"
                    >
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>


            <!-- Outer container - fixed height -->
{{--            <div class="border rounded-lg border-b-0 shadow-inner p-2 mt-4 overflow-hidden h-[77vh]">--}}
            <div class="border rounded-lg border-b-0 shadow-inner p-2 mt-4 overflow-hidden menu-height h-[77vh]">

            <!-- Scrollable product grid with proper height and padding -->
                <div id="menuItems"
                     class="grid grid-cols-1 sm:grid-cols-2  lg:grid-cols-3 xl:grid-cols-6
                gap-4 overflow-y-auto h-full pr-2 pb-[6rem] no-scrollbar">
                </div>
            </div>


        </section>

    </main>

    <aside id="cartPanel" class="hidden w-72 bg-gray-100 p-6 border-l overflow-y-auto">
        <h2 class="text-xl font-bold mb-2">Cart - <span id="cartTableNumber"></span></h2>
        <div id="cartContent"></div>
    </aside>
    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 text-center max-w-sm w-full">
            <h2 class="text-green-600 font-bold text-xl mb-4">✅ Success</h2>
            <p id="successMessage">Order placed successfully!</p>
            <button onclick="closeModal('successModal')" class="mt-4 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                OK
            </button>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
        <div class="bg-white rounded-lg shadow-lg p-6 text-center max-w-sm w-full">
            <h2 class="text-red-600 font-bold text-xl mb-4">❌ Error</h2>
            <p id="errorMessage">Something went wrong!</p>
            <button onclick="closeModal('errorModal')" class="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                Close
            </button>
        </div>
    </div>
    <button id="unlockAudio" class="hidden"></button>

</div>
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.3/echo.iife.js"></script>
<script>
    let currentTable = null;
    let activeOrderId = null;
    const carts = {};
    const firstCategory = @json($firstCategoryName); // ✅ global scope
    const autoTargetTableName = '';
    const sessionTableId = @json(session('active_table_id'));
    const sessionTableName = @json(session('active_table_name'));



    // function toggleTableActive(el, label, id,name) {
    //     console.log('hit')
    //     currentTable = label;
    //     activeOrderId = id;
    //
    //
    //     document.querySelectorAll('.table-item').forEach(e => e.classList.remove('bg-green-100'));
    //     el.classList.add('bg-green-100');
    //
    //     document.querySelectorAll('.order-section').forEach(e => e.classList.add('hidden'));
    //     document.getElementById('order-table-' + id)?.classList.remove('hidden');
    //     document.getElementById('activeTableHeading').textContent = name;
    //
    //
    //     if (document.getElementById('addOnMenuSection').classList.contains('shown-' + id)) {
    //         showAddOn(label, id);
    //     } else {
    //         document.getElementById('addOnMenuSection')?.classList.add('hidden');
    //         document.getElementById('cartPanel')?.classList.add('hidden');
    //     }
    // }
    function toggleTableActive(el, label, id, name) {
        console.log('hit');
        currentTable = label;
        activeOrderId = id;

        // 🧠 Store in session
        fetch('{{ route('store.active.table') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ id: id, name: name })
        })
            .then(res => res.json())
            .then(data => console.log("✅ Table stored in session:", data));

        document.querySelectorAll('.table-item').forEach(e => e.classList.remove('bg-green-100'));
        el.classList.add('bg-green-100');

        document.querySelectorAll('.order-section').forEach(e => e.classList.add('hidden'));
        document.getElementById('order-table-' + id)?.classList.remove('hidden');
        document.getElementById('activeTableHeading').textContent = name;

        if (document.getElementById('addOnMenuSection').classList.contains('shown-' + id)) {
            showAddOn(label, id);
        } else {
            document.getElementById('addOnMenuSection')?.classList.add('hidden');
            document.getElementById('cartPanel')?.classList.add('hidden');
        }
    }

    function startOrder(name, id) {
        currentTable = name;
        activeOrderId = id;
        from='start';
        const emptySection = document.getElementById('empty-order-' + id);
        if (emptySection) emptySection.classList.add('hidden');
        showAddOn(name, id,from);
    }

    function showAddOn(name, id,from = '') {
        console.log(from)
        currentTable = name;
        activeOrderId = id;
        document.getElementById('menuTableName').textContent = name;
        document.getElementById('cartTableNumber').textContent = name;
        document.getElementById('addOnMenuSection')?.classList.remove('hidden');
        document.getElementById('cartPanel')?.classList.remove('hidden');
        document.getElementById('addOnMenuSection').classList.add('shown-' + id);
        document.getElementById('add-on-' + id)?.classList.add('hidden');
        const menuContainer = document.querySelector('#addOnMenuSection .menu-height');
        if (menuContainer) {
            // Clear old height classes
            menuContainer.classList.remove('h-[40vh]', 'h-[77vh]');

            // Determine whether to use 77vh or 40vh based on order existence
            const hasMainOrder = !!getActiveParentOrderId(id);
            menuContainer.classList.add(hasMainOrder ? 'h-[40vh]' : 'h-[77vh]');
        }
        fetchMenu(firstCategory);
        renderCart(id);
    }

    function addToCart(product_id,name, price,image) {
        if (!carts[activeOrderId]) {
            carts[activeOrderId] = {};
        }
        console.log(name,price,image)

        const cart = carts[activeOrderId];

        if (cart[name]) {
            cart[name].quantity += 1;
        } else {
            cart[name] = { name: name, price: price,image_path:image, quantity: 1 ,            product_id: product_id,
            };
        }

        renderCart(activeOrderId);
    }

    function changeQuantity(name, delta) {
        const cart = carts[activeOrderId];
        if (cart && cart[name]) {
            cart[name].quantity += delta;
            if (cart[name].quantity <= 0) {
                delete cart[name];
            }
            renderCart(activeOrderId);
        }
    }

    function renderCart(id) {
        const cartPanel = document.getElementById("cartContent");
        cartPanel.innerHTML = '';

        const cart = carts[id] || {};
        const items = Object.values(cart);

        if (items.length === 0) {
            cartPanel.innerHTML = '<p class="text-sm text-gray-500">There are currently no orders in the cart.</p>';
            return;
        }
        console.log(items)

        items.forEach(item => {
            const div = document.createElement('div');
            div.className = "flex items-center gap-2 mb-3 bg-white p-2 rounded-lg";
            div.innerHTML = `
                <img src="${item.image_path}" class="w-12 h-12 object-cover rounded-md" />
                <div class="flex-1">
                    <p class="font-medium text-sm">${item.name}</p>
                    <p class="font-medium text-sm">${item.price} MMK</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="changeQuantity('${item.name}', -1)" class="bg-red-500 text-white w-6 h-6 rounded-full text-xs flex items-center justify-center">−</button>
                    <span class="font-bold text-sm">${item.quantity}</span>
                    <button onclick="changeQuantity('${item.name}', 1)" class="bg-green-500 text-white w-6 h-6 rounded-full text-xs flex items-center justify-center">+</button>
                </div>
            `;
            cartPanel.appendChild(div);
        });

        const total = items.reduce((sum, item) => sum + item.price * item.quantity, 0);

        cartPanel.innerHTML += `
            <div class="mt-4 flex justify-between text-lg font-semibold">
                <span>Total</span>
                <span>${total.toLocaleString()} MMK</span>
            </div>
            <button onclick="confirmOrder()" class="mt-4 bg-green-700 hover:bg-green-800 text-white font-semibold w-full py-3 rounded-full">
                Confirm Order
            </button>
        `;
    }

    function fetchMenu(categoryName) {
        console.log(categoryName);
        fetch(`/ajax/waiter-products?category=${categoryName}`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('menuItems').innerHTML = html;
                updateActiveTab(categoryName);
            });
    }

    function updateActiveTab(categoryName) {
        document.querySelectorAll('.menu-tab').forEach(btn => {
            if (btn.dataset.category === categoryName) {
                btn.classList.add('bg-green-600', 'text-white');
                btn.classList.remove('bg-gray-200', 'text-gray-700');
            } else {
                btn.classList.remove('bg-green-600', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            }
        });
    }


    // Load first category on DOM ready
    document.addEventListener("DOMContentLoaded", function () {
        const firstCategory = @json($firstCategoryName);
        console.log(firstCategory);
        if (firstCategory) {
            fetchMenu(firstCategory);
            updateActiveTab(firstCategory);
        }

        // const firstTable = document.querySelector('.table-item');
        // if (firstTable) {
        //     const onclickAttr = firstTable.getAttribute('onclick');
        //     const args = onclickAttr?.match(/toggleTableActive\(.*?'(.*?)',\s*'(.*?)',\s*'(.*?)'\)/);
        //     if (args && args.length === 4) {
        //         toggleTableActive(firstTable, args[1], args[2], args[3]);
        //     }
        // }
        // Auto-select active table from session (if available)
        if (sessionTableId && sessionTableName) {
            const targetItem = Array.from(document.querySelectorAll('.table-item'))
                .find(el => el.getAttribute('onclick')?.includes(`'${sessionTableId}'`) && el.textContent.includes(sessionTableName));

            if (targetItem) {
                toggleTableActive(targetItem, `Table ${sessionTableId}`, sessionTableId, sessionTableName);
            }
        } else {
            // fallback to first table
            const firstTable = document.querySelector('.table-item');
            if (firstTable) {
                const onclickAttr = firstTable.getAttribute('onclick');
                const args = onclickAttr?.match(/toggleTableActive\(.*?'(.*?)',\s*'(.*?)',\s*'(.*?)'\)/);
                if (args && args.length === 4) {
                    toggleTableActive(firstTable, args[1], args[2], args[3]);
                }
            }
        }

        Pusher.logToConsole = true;

        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: "{{ config('broadcasting.connections.pusher.key') }}",
            cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
            forceTLS: true,
            namespace: ''
        });

        console.log("✅ Echo initialized");
        Echo.channel('orders')
            .listen('OrderStatusUpdated', (e) => {
                console.log("🔔 Order changed:", e);
                if(e.order.order_type="dine_in"){
                    const sound = document.getElementById('notiSound');
                    if (sound) sound.play().catch(err => console.error("🔇 Sound error:", err));
                    const msg = `🛎️ Die In Order Updated `;
                    showToast(msg); // defined below
                }

            });

        Echo.channel('orders')
            .listen('OrderCreated', (e) => {
                console.log("📦 .listen('OrderCreated') fired", e);
                if(e.order.order_type="dine_in"){
                    const sound = document.getElementById('notiSound');
                    if (sound) sound.play().catch(err => console.error("🔇 Sound error:", err));
                    const msg = `🛎️ New Die In Order Created`;
                    showToast(msg); // defined below
                }

            });

        Echo.channel('orders')
            .listen('App\\Events\\OrderCreated', (e) => {
                console.log("📦 .listen('App\\\\Events\\\\OrderCreated') fired", e);
            });

        Echo.channel('orders')
            .listen('OrderStatusUpdated', (e) => {
                console.log("🔁 OrderStatusUpdated:", e);
            });

        setTimeout(() => {
            console.log("🔍 Channels registered:", Echo.connector.channels);
        }, 2000);


    });
    function getActiveParentOrderId(tableId) {
        const el = document.querySelector(`#order-table-${tableId}`);
        const id = el?.getAttribute('data-parent-order-id');
        return id || null;
    }

    function showModal(modalId, message) {
        const modal = document.getElementById(modalId);
        const messageEl = modal.querySelector('p');
        if (messageEl && message) messageEl.textContent = message;
        modal.classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        location.reload()
    }
    // Placeholder confirm action
    function confirmOrder() {
        const cart = carts[activeOrderId];
        console.log(cart);
        if (!cart || Object.keys(cart).length === 0) {
            alert("Cart is empty.");
            return;
        }


        const cartItems = Object.values(cart).map(item => ({
            product_id: item.product_id, // ✅ required
            name: item.name,             // ✅ required
            price: item.price,
            qty: item.quantity
        }));


        const payload = {
            parent_order_id: getActiveParentOrderId(activeOrderId),
            table_id: activeOrderId,
            cart: cartItems,
        };

        fetch('/waiter/checkout', {
                method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json', // 👈 Add this
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
                body: JSON.stringify(payload),
        })
            .then(async (res) => {
                const text = await res.text();
                try {
                    const data = JSON.parse(text);
                    console.log("Parsed Response:", data);

                    if (!res.ok || data.success === false) {
                        alert(data.message || "Something went wrong.");
                        throw new Error(data.message || "Failed to process");
                    }

                    // ✅ Success flow
                    showModal('successModal', data.message || "Order placed successfully!");

                    carts[activeOrderId] = {};
                    renderCart(activeOrderId);

                } catch (err) {
                    console.error("JSON parse error or failed:", err);
                    showModal('errorModal', "Something went wrong while submitting the request.");
                }
            })
            .catch(error => {
                console.error("Network or other error:", error);
                alert("Something went wrong while submitting the request.");
            });

    }
    function showToast(message) {
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.className = "fixed top-6 right-6 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-[100]";
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.remove();
        }, 2000);
        setTimeout(() => {
             location.reload();
        },2500);

    }

</script>
</body>
</html>
