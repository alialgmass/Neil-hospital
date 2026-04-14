<?php

use Illuminate\Support\Facades\Route;
use Modules\Reporting\Controllers\ReportController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::redirect('/', '/reports/daily');

        Route::get('/daily', [ReportController::class, 'daily'])
            ->middleware('can:reports.clinical')
            ->name('daily');

        Route::get('/income', [ReportController::class, 'income'])
            ->middleware('can:reports.financial')
            ->name('income');
    });
});
