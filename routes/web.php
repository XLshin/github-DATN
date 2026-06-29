<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ImeiController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PointController as AdminPointController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ShipmentController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\WarrantyController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\CarrierWebhookController;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
});

// Webhook endpoints
Route::post('/webhook/payment', [WebhookController::class, 'paymentCallback']);
Route::post('/webhook/carrier/{code}', [CarrierWebhookController::class, 'handle']);

/*
|--------------------------------------------------------------------------
| Guest authentication (US01-US04)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

    Route::get('/forgot-password', [PasswordController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password', [PasswordController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'resetPassword'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated user routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn() => view('client.profile.dashboard'))->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/change-password', [PasswordController::class, 'showChangePassword'])->name('password.change');
    Route::put('/change-password', [PasswordController::class, 'changePassword'])->name('password.change.update');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::post('/checkout/preview', [CheckoutController::class, 'preview'])->name('checkout.preview');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('/my-points', [PointController::class, 'index'])->name('points.index');
    Route::get('/point-history', [PointController::class, 'history'])->name('points.history');
});

/*
|--------------------------------------------------------------------------
| Admin routes (auth + admin middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->group(function () {
    Route::redirect('/admin', '/admin/dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('users', AdminUserController::class);
        Route::resource('products', AdminProductController::class);
        Route::delete('product-images/{image}', [AdminProductController::class, 'destroyImage'])->name('products.image.destroy');

        Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');

        Route::get('shipments/lookup', [ShipmentController::class, 'lookup'])->name('shipments.lookup');
        Route::get('shipments/create-from-order/{order}', [ShipmentController::class, 'createFromOrder'])->name('shipments.createFromOrder');
        Route::post('shipments/store-from-order/{order}', [ShipmentController::class, 'storeFromOrder'])->name('shipments.storeFromOrder');
        Route::patch('shipments/{shipment}/status', [ShipmentController::class, 'updateStatus'])->name('shipments.updateStatus');
        Route::resource('shipments', ShipmentController::class)->only(['index', 'show']);

        Route::get('warranties/lookup-imei', [WarrantyController::class, 'lookupImei'])->name('warranties.lookupImei');
        Route::patch('warranties/{warranty}/status', [WarrantyController::class, 'updateStatus'])->name('warranties.updateStatus');
        Route::resource('warranties', WarrantyController::class)->except(['destroy']);

        Route::resource('imeis', ImeiController::class);
        Route::resource('shipments', ShipmentController::class)->only(['index', 'show', 'create', 'store']);
        Route::resource('inventory', InventoryController::class);
        Route::get('/stocks', [ImeiController::class, 'stock'])->name('stocks');
        Route::get('/stocks/accessories', [ImeiController::class, 'accessoryStock'])->name('stocks.accessories');
    });

    Route::prefix('admin')->group(function () {
        Route::resource('categories', CategoryController::class);
        Route::resource('brands', BrandController::class);
        Route::resource('coupons', CouponController::class);

        Route::get('points', [AdminPointController::class, 'index'])->name('admin.points.index');
        Route::get('points/{user}', [AdminPointController::class, 'show'])->name('admin.points.show');
        Route::post('points/{user}/add', [AdminPointController::class, 'addPoints'])->name('admin.points.add');
        Route::post('points/{user}/deduct', [AdminPointController::class, 'deductPoints'])->name('admin.points.deduct');
        Route::post('points/{user}/reset', [AdminPointController::class, 'reset'])->name('admin.points.reset');

        Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
        Route::patch('reviews/{review}/hide', [ReviewController::class, 'hide'])->name('reviews.hide');
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    });
});
