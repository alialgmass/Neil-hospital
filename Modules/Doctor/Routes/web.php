<?php

use Illuminate\Support\Facades\Route;
use Modules\Doctor\Controllers\DoctorClaimsController;
use Modules\Doctor\Controllers\DoctorController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Doctors management
    Route::prefix('doctors')->name('doctors.')->group(function () {
        Route::get('/', [DoctorController::class, 'index'])
            ->middleware('can:doctors.view')
            ->name('index');

        Route::post('/', [DoctorController::class, 'store'])
            ->middleware('can:doctors.write')
            ->name('store');

        Route::put('/{id}', [DoctorController::class, 'update'])
            ->middleware('can:doctors.write')
            ->name('update');
    });

    // Doctor claims and payments
    Route::prefix('dr-claims')->name('dr-claims.')->group(function () {
        Route::get('/', [DoctorClaimsController::class, 'index'])
            ->middleware('can:drpayments.view')
            ->name('index');

        Route::get('/calculate', [DoctorClaimsController::class, 'calculate'])
            ->middleware('can:drpayments.view')
            ->name('calculate');

        Route::post('/pay', [DoctorClaimsController::class, 'pay'])
            ->middleware('can:drpayments.write')
            ->name('pay');
    });
});
