<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use Illuminate\Support\Facades\Route;

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
