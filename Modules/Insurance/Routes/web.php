<?php

use Illuminate\Support\Facades\Route;
use Modules\Insurance\Controllers\InsuranceCompanyController;
use Modules\Insurance\Controllers\PriceListController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Insurance Companies
    Route::prefix('insurance')->name('insurance.')->group(function () {
        Route::get('/', [InsuranceCompanyController::class, 'index'])
            ->middleware('can:insurance.view')
            ->name('index');

        Route::post('/', [InsuranceCompanyController::class, 'store'])
            ->middleware('can:insurance.write')
            ->name('store');

        Route::put('/{id}', [InsuranceCompanyController::class, 'update'])
            ->middleware('can:insurance.write')
            ->name('update');
    });

    // Price Lists
    Route::prefix('price-lists')->name('price-lists.')->group(function () {
        Route::get('/', [PriceListController::class, 'index'])
            ->middleware('can:insurance.view')
            ->name('index');

        Route::post('/', [PriceListController::class, 'store'])
            ->middleware('can:insurance.write')
            ->name('store');
    });
});
