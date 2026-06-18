<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\Admin\DashboardController;

Route::get('/', function () {
    return view('welcome');
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
