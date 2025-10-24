<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Kitchen\KitchenController;
use App\Http\Controllers\Waiter\WaiterController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Helpers\CheckRoute;
use App\Http\Controllers\Admin\TaxController;
use App\Http\Controllers\Manager\HomeController as ManagerHomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ____________________      Customer Section _______________________
Route::get('/', [HomeController::class, 'index'])->name('customer.index');
//------ ---- ---- Take Away  Customer Section ------------------
Route::get('/take-away/home', [HomeController::class, 'takeAwayHome'])->name('customer.take_away.home');
Route::get('/take-away/order-history', [OrderController::class, 'takeAwayOrderHistory'])->name('customer.take_away.order_history');
Route::get('/take-away/cart', [CartController::class, 'takeAwayCart'])->name('customer.take_away.cart');
Route::get('/take-away/checkout', [CartController::class, 'takeAwayCheckout'])->name('customer.take_away.checkout');
Route::post('/take-away/checkout', [OrderController::class, 'storeOrder'])->name('customer.take_away.checkout.store');
Route::get('/take-away/order/{id}', [OrderController::class, 'orderDetail'])->name('customer.take_away.order_detail');

Route::get('/ajax/cart/validate-stock', [CartController::class, 'validateStock'])
    ->name('ajax.cart.validate');
// ------ Ajax add cart
Route::post('/take-away/cart/add', [CartController::class, 'addAjax'])->name('customer.take_away.cart.add');
Route::get('/ajax/products', [HomeController::class, 'ajaxProducts'])->name('ajax.products');
Route::get('/ajax/waiter-products', [HomeController::class, 'ajaxProductsWaiter'])->name('ajax.products-waiter');
Route::post('/cart/update-ajax', [CartController::class, 'updateAjax'])->name('cart.update.ajax');
Route::get('/ajax/cart', [CartController::class, 'fetchCartHtml'])->name('ajax.cart.fetch');
Route::get('/ajax/cart-total', [CartController::class, 'fetchCartTotal'])->name('ajax.cart.fetch-total');
Route::post('/cart/tax/set', [CartController::class, 'setCartTax'])
    ->name('cart.tax.set');
Route::post('/cart/remove-ajax', [CartController::class, 'removeAjax'])->name('cart.remove.ajax');
Route::post('/cart/comment', [CartController::class, 'updateComment'])->name('cart.comment.ajax');

Route::get('/ajax/cart/counts', [CartController::class, 'counts'])
    ->name('ajax.cart.counts');
Route::get('/take-away', function () {
    return CheckRoute::handleTakeAwayEntry();
});
//------------   Die_in Customer Section ----------
Route::get('/customer/die-in/scanner', [HomeController::class, 'scanner'])->name('customer.die_in.scanner');
Route::get('/customer/die-in/validate', [HomeController::class, 'checkTableName'])->name('customer.die_in.validate');
Route::get('/die-in/home', [HomeController::class, 'dieInHome'])->name('customer.die_in.home');
Route::get('/die-in/cart', [CartController::class, 'dieInCart'])->name('customer.die-in.cart');
Route::post('/die-in/checkout', [OrderController::class, 'storeOrderDieIn'])->name('customer.die_in.checkout.store');
Route::get('/die-in/order-history', [OrderController::class, 'DieInOrderHistory'])->name('customer.die_in.order_history');
Route::get('/die-in/order/{id}', [OrderController::class, 'DieInOrderDetail'])->name('customer.die_in.order_detail');
Route::get('/die-in', function () {
    return CheckRoute::handleDineInEntry();
});
Route::get('/dine-in/entry', [HomeController::class, 'dineInEntry'])->name('customer.die_in.entry');
Route::post('/dine-in/forget', [HomeController::class, 'forgetTable'])->name('customer.die_in.forget');




// _____________________ Kitchen Section _____________________________
Route::get("/kitchen/login", [LoginController::class, 'KitchenLoginPage'])->name('kitchen.login');
Route::post("/kitchen/login", [LoginController::class, 'KitchenLogin']);
Route::get('/kitchen/forgot-password', [LoginController::class, 'kitchenForgotPasswordPage'])->name('kitchen.forgot_password');
Route::post('/kitchen/send-otp', [LoginController::class, 'sendOtpKitchen'])->name('kitchen.send_otp');
Route::get('/kitchen/verify-otp', [LoginController::class, 'showOtpKitchenPage'])->name('kitchen.verify_otp');
Route::post('/kitchen/verify-otp', [LoginController::class, 'verifyKitchenOtpOnly'])->name('kitchen.verify_otp_only');
Route::get('/kitchen/reset-password', [LoginController::class, 'kitchenResetPasswordPage'])->name('kitchen.reset_password');
Route::post('/kitchen/reset-password', [LoginController::class, 'resetPasswordKitchen'])->name('kitchen.reset_password_only');
Route::middleware(['auth', 'role:kitchen'])->group(function () {
    Route::get('/kitchen/home', [KitchenController::class, 'kitchenOrders'])->name('kitchen.home');
    Route::post('/orders/{order}/status', [KitchenController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('/kitchen/logout', [LoginController::class, 'kitchenLogout'])->name('kitchen.logout');
    Route::post('/orders/{order}/cancel', [KitchenController::class, 'cancel'])->name('orders.cancel');
});

Route::get('/kitchen', function () {
    return CheckRoute::handleKitchenEntry();
});

// ____________________ Waiter Section ____________________________
Route::get("/waiter/login", [LoginController::class, 'waiterLoginPage'])->name('waiter.login');
Route::post("/waiter/login", [LoginController::class, 'waiterLogin']);
Route::get('/waiter/forgot-password', [LoginController::class, 'waiterForgotPasswordPage'])->name('waiter.forgot_password');
Route::post("/waiter/forgot-password", [LoginController::class, 'waiterForgotPassword']);
Route::post('/waiter/send-otp', [LoginController::class, 'sendOtpWaiter'])->name('waiter.send_otp');
Route::get('/waiter/verify-otp', [LoginController::class, 'showOtpPage'])->name('waiter.verify_otp');
Route::post('/waiter/verify-otp', [LoginController::class, 'verifyOtpOnly'])->name('waiter.verify_otp_only');
Route::get('/waiter/reset-password', [LoginController::class, 'waiterResetPasswordPage'])->name('waiter.reset_password');
Route::post('/waiter/reset-password', [LoginController::class, 'resetPasswordWatier'])->name('waiter.reset_password_only');
Route::middleware(['auth', 'role:waiter'])->group(function () {
    Route::get('/waiter/home', [WaiterController::class, 'waiterOrders'])->name('waiter.home');
    Route::post('/waiter/checkout', [OrderController::class, 'storeAddOnOrder'])->name('waiter.checkout.store');
    Route::get('/waiter/table-order-section/{tableId}', [WaiterController::class, 'renderTableOrderSection']);
    Route::post('/store-active-table', function (\Illuminate\Http\Request $request) {
        session([
            'active_table_id' => $request->input('id'),
            'active_table_name' => $request->input('name'),
        ]);
        return response()->json(['success' => true]);
    })->name('store.active.table');
    Route::post('/waiter/logout', [LoginController::class, 'waiterLogout'])->name('waiter.logout');
});
Route::get('/waiter', function () {
    return CheckRoute::handleWaiterEntry();
});

// ____________________  Admin Section __________________________
Route::get("/admin/login", [LoginController::class, 'AdminloginPage'])->name('admin.login');
Route::post("/admin/login", [LoginController::class, 'Adminlogin']);
Route::get('/admin/forgot-password', [LoginController::class, 'adminForgotPasswordPage'])->name('admin.forgot_password');
Route::post('/admin/send-otp', [LoginController::class, 'sendOtpAdmin'])->name('admin.send_otp');
Route::get('/admin/verify-otp', [LoginController::class, 'showOtpPageAdmin'])->name('admin.verify_otp');
Route::post('/admin/verify-otp', [LoginController::class, 'verifyOtpOnlyAdmin'])->name('admin.verify_otp_only');
Route::get('/admin/reset-password', [LoginController::class, 'adminResetPasswordPage'])->name('admin.reset_password');
Route::post('/admin/reset-password', [LoginController::class, 'resetPasswordAdmin'])->name('admin.reset_password_only');
Route::get('/admin', function () {
    return CheckRoute::handleAdminEntry();
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::patch('/admin/orders/{order}/status', [AdminHomeController::class, 'updateStatus'])
        ->name('admin.orders.updateStatus');
    Route::get('/admin/orders/ajax', [AdminHomeController::class, 'ajaxFilteredOrders'])->name('admin.orders.ajax');
    Route::get('/admin/home', [AdminHomeController::class, 'home'])->name('admin.home');
    Route::get('/admin/ajax-dashboard-stats', [AdminHomeController::class, 'ajaxDashboardStats'])->name('admin.ajax.dashboard');
    Route::get('/admin/users/admin-users', [UserController::class, 'adminUserShow'])->name('admin.users.admin');
    Route::get('/admin/users/manager-users', [UserController::class, 'managerUserShow'])->name('admin.users.manager');
    Route::get('/admin/users/kitchen-users', [UserController::class, 'kitchenUserShow'])->name('admin.users.kitchen');
    Route::get('/admin/users/waiter-users', [UserController::class, 'waiterUserShow'])->name('admin.users.waiter');
    Route::get('/admin/users/customer-users', [UserController::class, 'customerUserShow'])->name('admin.users.customer');
    Route::get('/admin/users/user-profile', [UserController::class, 'showMyProfile'])->name('admin.users.user_profile');

    // User
    Route::get('/admin/users/create', [UserController::class, 'createUserPage'])->name('admin.users.create');
    Route::post('/admin/users/create', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/show', [UserController::class, 'show'])->name('admin.users.show');
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/admin/logout', [LoginController::class, 'Adminlogout'])->name('admin.logout');

    // Table
    Route::get('/admin/tables', [TableController::class, 'allTableShow'])->name('admin.tables.all');
    Route::get('/admin/tables/create', [TableController::class, 'createPage'])->name('admin.tables.create');
    Route::post('/admin/tables/store', [TableController::class, 'store'])->name('admin.tables.store');
    Route::get('/admin/tables/{table}/show', [TableController::class, 'show'])->name('admin.tables.show');
    Route::get('/admin/tables/{table}/edit', [TableController::class, 'edit'])->name('admin.tables.edit');
    Route::put('/admin/tables/{table}', [TableController::class, 'update'])->name('admin.tables.update');
    Route::delete('/admin/tables/{table}', [TableController::class, 'destroy'])->name('admin.tables.destroy');

    // Category
    Route::get('/admin/categories', [CategoryController::class, 'allCategoryShow'])->name('admin.categories.all');
    Route::get('/admin/categories/create', [CategoryController::class, 'createPage'])->name('admin.categories.create');
    Route::post('/admin/categories/store', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('/admin/categories/{category}/show', [CategoryController::class, 'show'])->name('admin.categories.show');
    Route::get('/admin/categories/{category}/edit', [CategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/admin/categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/admin/categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');

    // Product
    Route::get('/admin/products', [ProductController::class, 'allProductShow'])->name('admin.products.all');
    Route::get('/admin/products/create', [ProductController::class, 'createPage'])->name('admin.products.create');
    Route::post('/admin/products/store', [ProductController::class, 'store'])->name('admin.products.store');
    Route::get('/admin/products/{product}/show', [ProductController::class, 'show'])->name('admin.products.show');
    Route::get('/admin/products/{product}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
    Route::put('/admin/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/admin/products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');

    // Orders
    Route::get('/admin/orders/all', [OrderController::class, 'allOrders'])->name('admin.orders.all');
    Route::get('admin/unpaid/orders', [OrderController::class, 'unpaidOrders'])->name('admin.unpaid.orders');
    Route::get('/orders/filter', [OrderController::class, 'filterOrders'])->name('admin.orders.filter');
    Route::get('/admin/unpaid/order/filter', [OrderController::class, 'unpaidFilterOrders'])->name('admin.unpaid.orders.filter');

    // Voucher Generator
    Route::get('/orders/{order}/slip', [OrderController::class, 'show'])->name('orders.slip');
    Route::get('/admin/orders/{order}', [OrderController::class, 'showOrder'])->name('admin.orders.show');
    Route::patch('admin/data/orders/{order}/status', [OrderController::class, 'updateStatusAdmin'])->name('admin.orders.status');
    Route::post('admin/data/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('admin.orders.cancel');

    Route::prefix('admin/taxes')->name('admin.taxes.')->group(function () {
        Route::get('/',           [TaxController::class, 'index'])->name('index');
        Route::get('/create',     [TaxController::class, 'create'])->name('create');
        Route::post('/',          [TaxController::class, 'store'])->name('store');
        Route::get('/{tax}/edit', [TaxController::class, 'edit'])->name('edit');
        Route::put('/{tax}',      [TaxController::class, 'update'])->name('update');
        Route::delete('/{tax}',   [TaxController::class, 'destroy'])->name('destroy');

        // optional quick actions
        Route::patch('/{tax}/toggle-active', [TaxController::class, 'toggleActive'])->name('toggleActive');
        Route::patch('/{tax}/make-default',  [TaxController::class, 'makeDefault'])->name('makeDefault');
    });
});


// ________________ Manager Section __________________________________
Route::get('/manager', function () {
    return CheckRoute::handleManagerEntry();
});
Route::get("/manager/login", [LoginController::class, 'ManagerLoginPage'])->name('manager.login');
Route::post("/manager/login", [LoginController::class, 'ManagerLogin']);
Route::get('/manager/forgot-password', [LoginController::class, 'managerForgotPasswordPage'])->name('manager.forgot_password');
Route::post('/manager/send-otp', [LoginController::class, 'sendOtpManager'])->name('manager.send_otp');
Route::get('/manager/verify-otp', [LoginController::class, 'showOtpPageManager'])->name('manager.verify_otp');
Route::post('/manager/verify-otp', [LoginController::class, 'verifyOtpOnlyManager'])->name('manager.verify_otp_only');
Route::get('/manager/reset-password', [LoginController::class, 'managerResetPasswordPage'])->name('manager.reset_password');
Route::post('/manager/reset-password', [LoginController::class, 'resetPasswordManager'])->name('manager.reset_password_only');
Route::middleware(['auth', 'role:manager'])->group(function () {
    Route::get('/manager/home', [ManagerHomeController::class, 'home'])->name('manager.home');
    Route::patch('/manager/orders/{order}/status', [ManagerHomeController::class, 'updateStatus'])->name('manager.orders.updateStatus');
    Route::get('/manager/orders/ajax', [ManagerHomeController::class, 'ajaxFilteredOrders'])->name('manager.orders.ajax');
    Route::get('/manager/ajax-dashboard-stats', [ManagerHomeController::class, 'ajaxDashboardStats'])->name('manager.ajax.dashboard');
    Route::post('/manager/logout', [LoginController::class, 'ManagerLogout'])->name('manager.logout');
    Route::get('/manager/users/kitchen-users', [UserController::class, 'kitchenUserShow'])->name('manager.users.kitchen');
    Route::get('/manager/users/{user}/show', [UserController::class, 'showManager'])->name('manager.users.show');
    Route::get('/manger/users/waiter-users', [UserController::class, 'waiterUserShow'])->name('manager.users.waiter');
    Route::get('/manager/users/customer-users', [UserController::class, 'customerUserShow'])->name('manager.users.customer');
    Route::get('/manager/users/user-profile', [UserController::class, 'showMyProfile'])->name('manager.users.user_profile');
    Route::get('/manager/tables', [TableController::class, 'allTableShow'])->name('manager.tables.all');
    Route::get('/manager/tables/{table}/show', [TableController::class, 'show'])->name('manager.tables.show');
    Route::put('/manager/users/{user}', [UserController::class, 'updateManager'])->name('manager.users.update');

    Route::get('/manager/categories', [CategoryController::class, 'allCategoryShow'])->name('manager.categories.all');
    Route::get('/manager/products', [ProductController::class, 'allProductShow'])->name('manager.products.all');
    Route::get('/manager/products/{product}/show', [ProductController::class, 'show'])->name('manager.products.show');

    Route::get('/manager/orders/all', [OrderController::class, 'allOrders'])->name('manager.orders.all');
    Route::get('manager/unpaid/orders', [OrderController::class, 'unpaidOrders'])->name('manager.unpaid.orders');
    Route::get('/manager/filter', [OrderController::class, 'filterOrders'])->name('manager.orders.filter');
    Route::get('/manager/unpaid/order/filter', [OrderController::class, 'unpaidFilterOrders'])->name('manager.unpaid.orders.filter');
    // Voucher Generator
    Route::get('/manager/orders/{order}/slip', [OrderController::class, 'showSlipManager'])->name('manager.orders.slip');
    Route::get('/manager/orders/{order}', [OrderController::class, 'showOrder'])->name('manager.orders.show');
    Route::patch('data/orders/{order}/status', [OrderController::class, 'updateStatusAdmin'])->name('manager.orders.status');
    Route::post('data/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('manager.orders.cancel');
});
