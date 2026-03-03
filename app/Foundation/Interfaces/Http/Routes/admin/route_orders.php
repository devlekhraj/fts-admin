<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('orders/list', [OrderController::class, 'list'])->name('admin.orders.api.list');
    Route::get('orders/{id}/details', [OrderController::class, 'details'])->name('admin.orders.api.details');
});
