<?php

declare(strict_types=1);

use App\Domains\Order\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('orders/list', [OrderController::class, 'index'])->name('admin.orders.api.list');
    Route::get('orders/{id}/details', [OrderController::class, 'show'])->name('admin.orders.api.details');
    Route::post('orders/{id}/warranty', [OrderController::class, 'warranty'])->name('admin.orders.api.warranty.generate');
    Route::post('orders/{id}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.api.status.update');
});
