<!--sidebar wrapper -->
<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div>
            <img src="assets/images/logo-icon.png" class="logo-icon" alt="logo icon">
        </div>
        <div>
            <h4 class="logo-text">FoodOrder</h4>
        </div>
        <div class="toggle-icon ms-auto"><i class='bx bx-arrow-to-left'></i>
        </div>
    </div>
    <!--navigation-->

    <ul class="metismenu" id="menu">
        {{-- Dashboard --}}
        <li class="{{ request()->routeIs(['admin.home', 'manager.home']) ? 'active' : '' }}">
            @php
                $homeRoute = '';
                if (Auth::check()) {
                    if (Auth::user()->role=='admin') {
                        $homeRoute = route('admin.home');
                    } elseif (Auth::user()->role=='manager') {
                        $homeRoute = route('manager.home');
                    }
                }
            @endphp

            <a href="{{ $homeRoute }}">
                <div class="parent-icon"><i class='bx bx-home-circle'></i></div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>

        {{-- Users Menu --}}
        <li class="{{ request()->routeIs('*users.*') ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="bx bx-user-circle"></i></div>
                <div class="menu-title">Users</div>
            </a>
            <ul class="{{ request()->routeIs('*users.*') ? 'mm-collapse mm-show' : '' }}">
                @auth
                    @if (Auth::user()->role=='admin')
                        <li>
                            <a href="{{ route('admin.users.admin') }}" class="{{ request()->routeIs('admin.users.admin') ? 'active' : '' }}">
                                <i class="bx bx-right-arrow-alt"></i>All Admin Users
                            </a>
                        </li>
                    @endif

                    @if (Auth::user()->role=='admin')
                        <li>
                            <a href="{{ route('admin.users.manager') }}" class="{{ request()->routeIs('admin.users.manager') ? 'active' : '' }}">
                                <i class="bx bx-right-arrow-alt"></i>All Manager Users
                            </a>
                        </li>
                    @endif
                    @if (Auth::user()->role=='admin')
                        <li>
                            <li>
                                <a href="{{ route('admin.users.waiter') }}" class="{{ request()->routeIs('*.users.waiter') ? 'active' : '' }}">
                                    <i class="bx bx-right-arrow-alt"></i>All Waiter Users
                                </a>
                            </li>
                        </li>
                    @endif
                    @if (Auth::user()->role=='admin')
                        <li>
                            <a href="{{ route('admin.users.kitchen') }}" class="{{ request()->routeIs('*.users.kitchen') ? 'active' : '' }}">
                                <i class="bx bx-right-arrow-alt"></i>All Kitchen Users
                            </a>
                        </li>
                    @endif
                    @if (Auth::user()->role=='admin')
                        <li>
                            <a href="{{ route('admin.users.customer') }}" class="{{ request()->routeIs('*.users.customer') ? 'active' : '' }}">
                                <i class="bx bx-right-arrow-alt"></i>All Customers
                            </a>
                        </li>
                    @endif

                    @if (Auth::user()->role=='manager')
                        <li>
                            <a href="{{ route('manager.users.waiter') }}" class="{{ request()->routeIs('*.users.waiter') ? 'active' : '' }}">
                                <i class="bx bx-right-arrow-alt"></i>All Waiter Users
                            </a>
                        </li>
                    @endif

                    @if (Auth::user()->role=='manager')
                        <li>
                            <a href="{{ route('manager.users.kitchen') }}" class="{{ request()->routeIs('*.users.kitchen') ? 'active' : '' }}">
                                <i class="bx bx-right-arrow-alt"></i>All Kitchen Users
                            </a>
                        </li>
                    @endif

                    @if (Auth::user()->role=='manager')
                        <li>
                            <a href="{{ route('manager.users.customer') }}" class="{{ request()->routeIs('*.users.customer') ? 'active' : '' }}">
                                <i class="bx bx-right-arrow-alt"></i>All Customers
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>
        </li>

        {{-- Tables Menu (Placeholders, adjust routes and names as you define them) --}}
        <li class="{{ request()->routeIs('*.tables.*') ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='fadeIn animated bx bx-chair'></i>
                </div>
                <div class="menu-title">Tables</div>
            </a>
            <ul class="{{ request()->routeIs('*.tables.*') ? 'mm-collapse mm-show' : '' }}">
                <li> <a href="{{ route('admin.tables.all') }}" class="{{ request()->routeIs('*.tables.all') ? 'active' : '' }}"><i class="bx bx-right-arrow-alt"></i>All Tables</a>
                </li>
                <li> <a href="{{route('admin.tables.create')}}" class="{{ request()->routeIs('*.tables.create') ? 'active' : '' }}"><i class="bx bx-right-arrow-alt"></i>Create Table</a>
                </li>
                {{-- <li> <a href="#" class="{{ request()->routeIs('*.tables.detail') ? 'active' : '' }}"><i class="bx bx-right-arrow-alt"></i>Table Detail</a>
                </li>
                <li> <a href="#" class="{{ request()->routeIs('*.tables.edit') ? 'active' : '' }}"><i class="bx bx-right-arrow-alt"></i>Edit Table</a>
                </li> --}}
            </ul>
        </li>

        {{-- Category Menu (Placeholders) --}}
        <li class="{{ request()->routeIs('*.category.*') ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="fadeIn animated bx bx-customize"></i>
                </div>
                <div class="menu-title">Category</div>
            </a>
            <ul class="{{ request()->routeIs('*.category.*') ? 'mm-collapse mm-show' : '' }}">
                <li> <a href="{{ route('admin.categories.all') }}" class="{{ request()->routeIs('*.category.all') ? 'active' : '' }}"><i class="bx bx-right-arrow-alt"></i>All Category</a>
                </li>
                <li> <a href="{{ route('admin.categories.create') }}" class="{{ request()->routeIs('*.category.create') ? 'active' : '' }}"><i class="bx bx-right-arrow-alt"></i>Create Category</a>
                </li>
            </ul>
        </li>

        {{-- Products Menu (Placeholders) --}}
        <li class="{{ request()->routeIs('*.products.*') ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='fadeIn animated bx bx-coffee-togo'></i>
                </div>
                <div class="menu-title">Products</div>
            </a>
            <ul class="{{ request()->routeIs('*.products.*') ? 'mm-collapse mm-show' : '' }}">
                <li> <a href="{{ route('admin.products.all') }}" class="{{ request()->routeIs('*.products.all') ? 'active' : '' }}"><i class="bx bx-right-arrow-alt"></i>All Products</a>
                </li>
                <li> <a href="{{ route('admin.products.create') }}" class="{{ request()->routeIs('*.products.create') ? 'active' : '' }}"><i class="bx bx-right-arrow-alt"></i>Add New Product</a>
                </li>
                {{-- <li> <a href="#" class="{{ request()->routeIs('*.products.detail') ? 'active' : '' }}"><i class="bx bx-right-arrow-alt"></i>Product Detail</a>
                </li>
                <li> <a href="#" class="{{ request()->routeIs('*.products.edit') ? 'active' : '' }}"><i class="bx bx-right-arrow-alt"></i>Edit Product</a>
                </li> --}}
            </ul>
        </li>

        {{-- Orders Menu (Placeholders) --}}
        <li class="{{ request()->routeIs('*.orders.*') ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class='fadeIn animated bx bx-task'></i>
                </div>
                <div class="menu-title">Orders</div>
            </a>
            <ul class="{{ request()->routeIs('*.orders.*') ? 'mm-collapse mm-show' : '' }}">
                <li> <a href="{{route('admin.orders.all','all')}}" class="{{ request()->routeIs('*.orders.all') ? 'active' : '' }}"><i class="bx bx-right-arrow-alt"></i>All Orders</a>
                </li>
                <li> <a href="{{route('admin.orders.all','unpaid')}}" class="{{ request()->routeIs('*.orders.all') ? 'active' : '' }}"><i class="bx bx-right-arrow-alt"></i>Unpaid Orders</a>
                </li>
{{--                 <li> <a href="#" class="{{ request()->routeIs('*.orders.takeaway') ? 'active' : '' }}"><i class="bx bx-right-arrow-alt"></i>Take Away Orders</a>--}}
{{--                </li>--}}
{{--                <li> <a href="#" class="{{ request()->routeIs('*.orders.dinein') ? 'active' : '' }}"><i class="bx bx-right-arrow-alt"></i>DieIn Orders</a>--}}
{{--                </li>--}}
{{--                <li> <a href="#" class="{{ request()->routeIs('*.orders.detail') ? 'active' : '' }}"><i class="bx bx-right-arrow-alt"></i>Order Detail</a>--}}
{{--                </li> --}}
            </ul>
        </li>
        <li class="{{ request()->routeIs('*.taxes.*') ? 'mm-active' : '' }}">
            <a href="javascript:;" class="has-arrow">
                <div class="parent-icon"><i class="fadeIn animated bx bx-customize"></i>
                </div>
                <div class="menu-title">Tax</div>
            </a>
            <ul class="{{ request()->routeIs('*.taxes.*') ? 'mm-collapse mm-show' : '' }}">
                <li> <a href="{{ route('admin.taxes.index') }}" class="{{ request()->routeIs('*.taxes.index') ? 'active' : '' }}"><i class="bx bx-right-arrow-alt"></i>All Taxes</a>
                </li>
                <li> <a href="{{ route('admin.taxes.create') }}" class="{{ request()->routeIs('*.taxes.create') ? 'active' : '' }}"><i class="bx bx-right-arrow-alt"></i>Create Tax</a>
                </li>
            </ul>
        </li>

        <li>
            <a href="{{ route('admin.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <div class="parent-icon"><i class='bx bx-log-out-circle'></i>
                </div>
                <div class="menu-title">Logout</div>
                <form id="logout-form" action="{{  Auth::user()->role=='admin' ? route('admin.logout') : route('manager.logout')}} " method="POST" style="display: none;">
                    @csrf
                </form>
            </a>
        </li>
    </ul>
    <!--end navigation-->
</div>
<!--end sidebar wrapper -->
