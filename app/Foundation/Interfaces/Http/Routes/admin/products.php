<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Foundation\Interfaces\Http\Controllers\Admin\Product\ProductsController;


// TODO: Protect with admin auth + permission middleware.

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('products', [ProductsController::class, 'productList'])->name('admin.products.index');
    Route::get('products/{id}', [ProductsController::class, 'show'])->name('admin.products.show');
    Route::post('products', [ProductsController::class, 'store'])->name('admin.products.store');
    Route::put('products/{id}', [ProductsController::class, 'update'])->name('admin.products.update');
    Route::delete('products/{id}', [ProductsController::class, 'destroy'])->name('admin.products.destroy');
});
