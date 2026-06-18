<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Http\Controllers\Admin\ImeiController;
use App\Http\Controllers\Admin\InventoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::resource('imeis', ImeiController::class);
Route::resource('inventory', InventoryController::class);
Route::get(
    '/stocks',
    [InventoryController::class,'stock']
)->name('stocks');