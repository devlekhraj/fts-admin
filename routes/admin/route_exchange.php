<?php

declare(strict_types=1);

use App\Domains\Exchange\Controllers\ExchangePricingController;
use App\Domains\Exchange\Controllers\ExchangeRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('exchange-requests', [ExchangeRequestController::class, 'index'])->name('admin.exchange-requests.index');
    Route::get('exchange-requests/{id}', [ExchangeRequestController::class, 'show'])->whereNumber('id')->name('admin.exchange-requests.show');
    Route::post('exchange-requests/{id}/status', [ExchangeRequestController::class, 'updateStatus'])->whereNumber('id')->name('admin.exchange-requests.status');

    Route::get('exchange-valuations', [ExchangePricingController::class, 'valuations'])->name('admin.exchange-valuations.index');
    Route::post('exchange-valuations', [ExchangePricingController::class, 'storeValuation'])->name('admin.exchange-valuations.store');
    Route::put('exchange-valuations/{id}', [ExchangePricingController::class, 'updateValuation'])->whereNumber('id')->name('admin.exchange-valuations.update');

    Route::get('exchange-damage-rules', [ExchangePricingController::class, 'rules'])->name('admin.exchange-damage-rules.index');
    Route::put('exchange-damage-rules/{id}', [ExchangePricingController::class, 'updateRule'])->whereNumber('id')->name('admin.exchange-damage-rules.update');
});
