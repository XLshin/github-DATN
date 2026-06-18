<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
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
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BrandController;


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
