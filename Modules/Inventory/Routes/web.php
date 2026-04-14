<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Controllers\InventoryController;
use Modules\Inventory\Controllers\PurchaseInvoiceController;
use Modules\Inventory\Controllers\SupplierController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Inventory items
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])
            ->middleware('can:inventory.view')
            ->name('index');

        Route::post('/', [InventoryController::class, 'store'])
            ->middleware('can:inventory.write')
            ->name('store');

        Route::put('/{id}', [InventoryController::class, 'update'])
            ->middleware('can:inventory.write')
            ->name('update');
    });

    // Suppliers
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])
            ->middleware('can:inventory.view')
            ->name('index');

        Route::post('/', [SupplierController::class, 'store'])
            ->middleware('can:inventory.write')
            ->name('store');

        Route::put('/{id}', [SupplierController::class, 'update'])
            ->middleware('can:inventory.write')
            ->name('update');
    });

    // Purchase invoices
    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::get('/', [PurchaseInvoiceController::class, 'index'])
            ->middleware('can:inventory.view')
            ->name('index');

        Route::post('/', [PurchaseInvoiceController::class, 'store'])
            ->middleware('can:inventory.write')
            ->name('store');
    });
});
