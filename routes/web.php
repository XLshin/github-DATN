<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;

Route::get('/', function () {
    return view('welcome');
});

// Redirect /admin -> /admin/products
Route::get('admin', fn() => redirect()->route('admin.products.index'));

// Admin routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('products', ProductController::class);
    Route::delete('products/{image}/image', [ProductController::class, 'destroyImage'])->name('products.image.destroy');
});
