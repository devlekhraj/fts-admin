<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Foundation\Interfaces\Http\Controllers\Admin\Product\ProductsController;
use App\Foundation\Interfaces\Http\Controllers\Admin\Product\ProductVariantController;
use App\Foundation\Interfaces\Http\Controllers\Admin\Product\ProductImageController;
use App\Foundation\Interfaces\Http\Controllers\Admin\Product\ProductListController;
use App\Foundation\Interfaces\Http\Controllers\Admin\Product\ProductVariantImageController;


// TODO: Protect with admin auth + permission middleware.

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('products', [ProductsController::class, 'productList'])->name('admin.products.index');
    Route::get('products/{id}', [ProductsController::class, 'show'])->name('admin.products.show');
    Route::get('products/{id}/faqs', [ProductsController::class, 'faqs'])->name('admin.products.faqs');
    Route::post('products', [ProductsController::class, 'store'])->name('admin.products.store');
    Route::post('products/{id}/variants', [ProductVariantController::class, 'store'])->name('admin.products.variants.store');
    Route::put('products/{id}/variants/{item_id}', [ProductVariantController::class, 'update'])->name('admin.products.variants.update');
    Route::put('products/{id}', [ProductsController::class, 'update'])->name('admin.products.update');
    Route::put('products/{id}/images/{fileUsageId}', [ProductImageController::class, 'update'])->name('admin.products.images.update');
    Route::delete('products/{id}/images/{fileUsageId}', [ProductImageController::class, 'delete'])->name('admin.products.images.delete');
    Route::delete('products/{id}', [ProductsController::class, 'destroy'])->name('admin.products.destroy');
    Route::put('product-variants/{variant_id}/images/{fileUsageId}', [ProductVariantImageController::class, 'update'])->name('admin.products.variants.images.update');
    Route::delete('product-variants/{variant_id}/images/{fileUsageId}', [ProductVariantImageController::class, 'delete'])->name('admin.products.variants.images.delete');

    Route::get('product-list', [ProductListController::class, 'getList'])
    ->name('product.list');
     


});
