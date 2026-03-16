<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\PaymentMethod\PaymentMethodController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('payment-methods', [PaymentMethodController::class, 'list'])->name('admin.payment-methods.list');
    Route::get('payment-methods/{id}/details', [PaymentMethodController::class, 'show'])->name('admin.payment-methods.show');
    Route::put('payment-methods/{id}', [PaymentMethodController::class, 'update'])->name('admin.payment-methods.update');

    Route::put('payment-methods/{id}/images/{fileUsageId}', [\App\Foundation\Interfaces\Http\Controllers\Admin\PaymentMethod\PaymentMethodImageController::class, 'update'])->name('admin.payment-methods.images.update');
    Route::delete('payment-methods/{id}/images/{fileUsageId}', [\App\Foundation\Interfaces\Http\Controllers\Admin\PaymentMethod\PaymentMethodImageController::class, 'delete'])->name('admin.payment-methods.images.delete');
});
