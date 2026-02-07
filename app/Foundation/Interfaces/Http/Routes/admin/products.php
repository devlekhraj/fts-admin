<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Foundation\Interfaces\Http\Controllers\Admin\ProductsController;

// TODO: Protect with admin auth + permission middleware.

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('products', [ProductsController::class, 'index']);
    Route::get('products/{id}', [ProductsController::class, 'show']);
    Route::post('products', [ProductsController::class, 'store']);
    Route::put('products/{id}', [ProductsController::class, 'update']);
    Route::delete('products/{id}', [ProductsController::class, 'destroy']);
});
