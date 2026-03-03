<?php

declare(strict_types=1);


use App\Foundation\Interfaces\Http\Controllers\Admin\Product\ProductBrandController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('brands', [ProductBrandController::class, 'brandList'])->name('admin.brands.index');
    Route::get('brands/{id}', [ProductBrandController::class, 'brandShow'])->name('admin.brands.show');
    Route::post('brands', [ProductBrandController::class, 'store'])->name('admin.brands.store');
    Route::put('brands/{id}', [ProductBrandController::class, 'update'])->name('admin.brands.update');
    Route::delete('brands/{id}', [ProductBrandController::class, 'destroy'])->name('admin.brands.destroy');
});
