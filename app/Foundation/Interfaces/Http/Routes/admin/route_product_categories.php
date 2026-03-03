<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Foundation\Interfaces\Http\Controllers\Admin\Product\ProductCategoryController;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('product-categories', [ProductCategoryController::class, 'categoryList'])->name('admin.product-categories.index');
    Route::get('product-categories/{id}', [ProductCategoryController::class, 'categoryShow'])->name('admin.product-categories.show');
    Route::put('product-categories/{id}', [ProductCategoryController::class, 'update'])->name('admin.product-categories.update');
});
