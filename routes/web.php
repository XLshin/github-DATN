<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Http\Controllers\Admin\ImeiController;
use App\Http\Controllers\Admin\InventoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;

Route::get('/', function () {
    return view('welcome');
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
