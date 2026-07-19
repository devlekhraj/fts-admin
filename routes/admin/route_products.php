<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Product Brands
use App\Domains\ProductBrand\Controllers\ProductBrandController;
use App\Domains\ProductBrand\Controllers\ProductBrandBannerController;
use App\Domains\ProductBrand\Controllers\ProductBrandImageController;

// Products
use App\Domains\Product\Controllers\ProductsController;
use App\Domains\Product\Controllers\ProductVariantController;
use App\Domains\Product\Controllers\ProductImageController;
use App\Domains\Product\Controllers\ProductListController;
use App\Domains\Product\Controllers\ProductVariantImageController;
use App\Domains\Product\Controllers\ProductGiftController;
use App\Domains\Product\Controllers\ProductImportController;

// Product Attributes 
use App\Domains\Product\Controllers\ProductAttributeController;


// Product Categories
use App\Domains\ProductCategory\Controllers\ProductCategoryController;
use App\Domains\ProductCategory\Controllers\ProductCategoryBannerController;
use App\Domains\ProductCategory\Controllers\ProductCategoryImageController;

// TODO: Protect with admin auth + permission middleware.


Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('brands', [ProductBrandController::class, 'brandList'])->name('admin.brands.index');
    Route::put('brands/reorder', [ProductBrandController::class, 'reorder'])->name('admin.brands.reorder');
    Route::get('brands/{id}', [ProductBrandController::class, 'brandShow'])->name('admin.brands.show');
    Route::get('brands/{id}/faqs', [ProductBrandController::class, 'faqs'])->name('admin.brands.faqs');
    Route::post('brands', [ProductBrandController::class, 'store'])->name('admin.brands.store');
    Route::put('brands/{id}', [ProductBrandController::class, 'update'])->name('admin.brands.update');
    Route::delete('brands/{id}', [ProductBrandController::class, 'destroy'])->name('admin.brands.destroy');
    Route::put('brands/{id}/categories', [ProductBrandController::class, 'syncCategories'])->name('admin.brands.categories.sync');
    Route::post('brands/{id}/banner', [ProductBrandBannerController::class, 'store'])->name('admin.brands.banner.store');
    Route::put('brands/{id}/banner/{fileUsageId}', [ProductBrandBannerController::class, 'update'])->name('admin.brands.banner.update');
    Route::delete('brands/{id}/banner/{fileUsageId}', [ProductBrandBannerController::class, 'delete'])->name('admin.brands.banner.delete');
    Route::put('brands/{id}/images/{fileUsageId}', [ProductBrandImageController::class, 'update'])->name('admin.brands.images.update');
    Route::delete('brands/{id}/images/{fileUsageId}', [ProductBrandImageController::class, 'delete'])->name('admin.brands.images.delete');

    Route::get('product-brand-list', [ProductBrandController::class, 'getList']);
});


Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {

    // Products
    Route::get('products', [ProductsController::class, 'productList'])->name('admin.products.index');
    Route::post('products/import/preview', [ProductImportController::class, 'preview'])->name('admin.products.import.preview');
    Route::post('products/import', [ProductImportController::class, 'store'])->name('admin.products.import');
    Route::get('products/{id}', [ProductsController::class, 'show'])->name('admin.products.show');
    Route::get('products/{id}/faqs', [ProductsController::class, 'faqs'])->name('admin.products.faqs');
    Route::post('products', [ProductsController::class, 'store'])->name('admin.products.store');
    Route::post('products/{id}/variants', [ProductVariantController::class, 'store'])->name('admin.products.variants.store');
    Route::put('products/{id}/variants/{item_id}', [ProductVariantController::class, 'update'])->name('admin.products.variants.update');
    Route::put('products/{id}', [ProductsController::class, 'update'])->name('admin.products.update');
    Route::put('products/{id}/price', [ProductsController::class, 'updatePrice'])->name('admin.products.price.update');
    Route::put('products/{id}/images/{fileUsageId}', [ProductImageController::class, 'update'])->name('admin.products.images.update');
    Route::delete('products/{id}/images/{fileUsageId}', [ProductImageController::class, 'delete'])->name('admin.products.images.delete');
    Route::delete('products/{id}/delete', [ProductsController::class, 'deleteProduct'])->name('admin.products.delete');
    Route::put('product-variants/{variant_id}/images/{fileUsageId}', [ProductVariantImageController::class, 'update'])->name('admin.products.variants.images.update');
    Route::delete('product-variants/{variant_id}/images/{fileUsageId}', [ProductVariantImageController::class, 'delete'])->name('admin.products.variants.images.delete');

    Route::get('products/{id}/gifts', [ProductGiftController::class, 'index'])->name('admin.products.gifts.index');
    Route::post('products/{id}/gifts', [ProductGiftController::class, 'sync'])->name('admin.products.gifts.store');
    Route::put('products/{id}/gifts', [ProductGiftController::class, 'sync'])->name('admin.products.gifts.sync');
    Route::delete('products/{id}/gifts/{giftId}', [ProductGiftController::class, 'destroy'])->name('admin.products.gifts.destroy');

    Route::get('product-list', [ProductListController::class, 'getList'])
    ->name('product.list');
     


    // Product Attributes
    Route::get('attribute-list', [ProductAttributeController::class, 'attributeList'])->name('admin.attribute.list');
    Route::get('attributes/{id}/detail', [ProductAttributeController::class, 'attributeDetail'])->name('admin.attribute.detail');
    Route::patch('attributes/{id}/items/{attributeId}', [ProductAttributeController::class, 'updateAttributeItem'])->name('admin.attribute.item.update');
    Route::patch('attributes/{id}/items/{attributeId}/values', [ProductAttributeController::class, 'updateAttributeValues'])->name('admin.attribute.values.update');

});

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('product-categories', [ProductCategoryController::class, 'categoryList'])->name('admin.product-categories.index');
    Route::get('product-categorie-list', [ProductCategoryController::class, 'getList'])->name('admin.product-categories.list-alt');
    Route::get('product-categories/{id}', [ProductCategoryController::class, 'categoryShow'])->name('admin.product-categories.show');
    Route::get('product-categories/{id}/faqs', [ProductCategoryController::class, 'faqs'])->name('admin.product-categories.faqs');
    Route::post('product-categories', [ProductCategoryController::class, 'store'])->name('admin.product-categories.store');
    Route::put('product-categories/reorder', [ProductCategoryController::class, 'reorder'])->name('admin.product-categories.reorder');
    Route::put('product-categories/{id}', [ProductCategoryController::class, 'update'])->name('admin.product-categories.update');
    Route::post('product-categories/{id}/banner', [ProductCategoryBannerController::class, 'store'])->name('admin.product-categories.banner.store');
    Route::put('product-categories/{id}/banner/{fileUsageId}', [ProductCategoryBannerController::class, 'update'])->name('admin.product-categories.banner.update');
    Route::delete('product-categories/{id}/banner/{fileUsageId}', [ProductCategoryBannerController::class, 'delete'])->name('admin.product-categories.banner.delete');
    Route::put('product-categories/{id}/images/{fileUsageId}', [ProductCategoryImageController::class, 'update'])->name('admin.product-categories.images.update');
    Route::delete('product-categories/{id}/images/{fileUsageId}', [ProductCategoryImageController::class, 'delete'])->name('admin.product-categories.images.delete');
    Route::delete('product-categories/{id}', [ProductCategoryController::class, 'destroy'])->name('admin.product-categories.destroy');
});
