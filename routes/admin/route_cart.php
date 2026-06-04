<?php

declare(strict_types=1);

use App\Domains\Cart\Controllers\CartController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('carts', [CartController::class, 'index'])->name('admin.cart.list');
    Route::get('carts/{id}', [CartController::class, 'show'])->name('admin.cart.detail');
    Route::delete('carts/{id}/delete', [CartController::class, 'destroy'])->name('admin.cart.delete');
});
