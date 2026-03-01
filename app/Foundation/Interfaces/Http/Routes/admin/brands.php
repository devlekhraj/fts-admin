<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\BrandsController;
use App\Foundation\Interfaces\Http\Controllers\Admin\Product\ProductBrandController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('brands', [ProductBrandController::class, 'brandList']);
    Route::get('brands/{id}', [BrandsController::class, 'show']);
    Route::post('brands', [BrandsController::class, 'store']);
    Route::put('brands/{id}', [BrandsController::class, 'update']);
    Route::delete('brands/{id}', [BrandsController::class, 'destroy']);
});
