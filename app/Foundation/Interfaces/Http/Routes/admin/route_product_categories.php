<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Foundation\Interfaces\Http\Controllers\Admin\Product\ProductCategoryController;
use App\Foundation\Interfaces\Http\Controllers\Admin\Product\ProductCategoryImageController;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('product-categories', [ProductCategoryController::class, 'categoryList'])->name('admin.product-categories.index');
    Route::get('product-categorie-list', [ProductCategoryController::class, 'getList'])->name('admin.product-categories.index');
    Route::get('product-categories/{id}', [ProductCategoryController::class, 'categoryShow'])->name('admin.product-categories.show');
    Route::post('product-categories', [ProductCategoryController::class, 'store'])->name('admin.product-categories.store');
    Route::put('product-categories/{id}', [ProductCategoryController::class, 'update'])->name('admin.product-categories.update');
    Route::put('product-categories/{id}/images/{fileUsageId}', [ProductCategoryImageController::class, 'update'])->name('admin.product-categories.images.update');
    Route::delete('product-categories/{id}/images/{fileUsageId}', [ProductCategoryImageController::class, 'delete'])->name('admin.product-categories.images.delete');
    Route::delete('product-categories/{id}', [ProductCategoryController::class, 'destroy'])->name('admin.product-categories.destroy');
});
