<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\Cart\CartController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('carts', [CartController::class, 'cartList'])->name('admin.cart.list');
    Route::get('carts/{id}', [CartController::class, 'cartDetail'])->name('admin.cart.detail');
});
