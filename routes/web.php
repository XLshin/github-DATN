<?php

use App\Http\Controllers\Admin\ImeiController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ShipmentController;
use App\Http\Controllers\Admin\WarrantyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\Admin\DashboardController;


Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');

    Route::get('/forgot-password', [PasswordController::class, 'showForgotPassword'])
        ->name('password.request');

    Route::post('/forgot-password', [PasswordController::class, 'sendResetLink'])
        ->name('password.email');

    Route::get('/reset-password', [PasswordController::class, 'showResetPassword'])
        ->name('password.reset');

    Route::post('/reset-password', [PasswordController::class, 'resetPassword'])
        ->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('auth.dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/change-password', [PasswordController::class, 'showChangePassword'])
        ->name('password.change');

    Route::put('/change-password', [PasswordController::class, 'changePassword'])
        ->name('password.change.update');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    });
});


Route::resource('imeis', ImeiController::class);
Route::resource('inventory', InventoryController::class);
Route::get(
    '/stocks',
    [InventoryController::class,'stock']
)->name('stocks');
// Redirect /admin -> /admin/products
Route::get('admin', fn() => redirect()->route('admin.products.index'));

// Admin routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('products', ProductController::class);
    Route::delete('products/{image}/image', [ProductController::class, 'destroyImage'])->name('products.image.destroy');
});

Route::prefix('admin')->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::resource('brands', BrandController::class);
});
Route::get('/', [HomeController::class, 'index']);

// Cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');

// Checkout (require auth)
Route::middleware('auth')->group(function () {
	Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
	Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
	Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');

	// User orders
	Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
	Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

// Admin order management (requires auth; controller checks role)
Route::prefix('admin')->middleware('auth')->group(function () {
	Route::get('orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
	Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
});
Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Vận chuyển
        Route::get('shipments/lookup', [ShipmentController::class, 'lookup'])
            ->name('shipments.lookup');

        Route::get('shipments/create-from-order/{order}', [ShipmentController::class, 'createFromOrder'])
            ->name('shipments.createFromOrder');

        Route::post('shipments/store-from-order/{order}', [ShipmentController::class, 'storeFromOrder'])
            ->name('shipments.storeFromOrder');

        Route::patch('shipments/{shipment}/status', [ShipmentController::class, 'updateStatus'])
            ->name('shipments.updateStatus');

        Route::resource('shipments', ShipmentController::class)
            ->only(['index', 'show']);

        // Bảo hành
        Route::get('warranties/lookup-imei', [WarrantyController::class, 'lookupImei'])
            ->name('warranties.lookupImei');

        Route::patch('warranties/{warranty}/status', [WarrantyController::class, 'updateStatus'])
            ->name('warranties.updateStatus');

        Route::resource('warranties', WarrantyController::class)
            ->except(['destroy']);
    });
Route::post(
    '/products/{product}/reviews',
    [ReviewController::class,'store']
)->middleware('auth')
->name('reviews.store');
Route::get(
    '/products/{product}',
    [ProductController::class, 'show']
)->name('products.show');
Route::patch(
    '/admin/reviews/{review}/hide',
    [ReviewController::class, 'hide']
)->name('reviews.hide');
Route::delete(
    '/admin/reviews/{review}',
    [ReviewController::class, 'destroy']
)->name('reviews.destroy');
Route::get(
    '/admin/reviews',
    [ReviewController::class, 'index']
)->name('reviews.index');


Route::get('/my-points', [PointController::class, 'index'])
    ->middleware('auth')
    ->name('points.index');

Route::get('/point-history', [PointController::class, 'history'])
    ->middleware('auth')
    ->name('points.history');
Route::get(
    '/admin/dashboard',
    [DashboardController::class, 'index']
)->name('admin.dashboard');
