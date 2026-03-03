<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\PaymentMethod\PaymentMethodController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('payment-methods', [PaymentMethodController::class, 'list'])->name('admin.payment-methods.list');
    Route::get('payment-methods/{id}/details', [PaymentMethodController::class, 'show'])->name('admin.payment-methods.show');
});
