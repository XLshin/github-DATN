<?php

use App\Http\Controllers\Admin\ShipmentController;
use App\Http\Controllers\Admin\WarrantyController;
use Illuminate\Support\Facades\Route;

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