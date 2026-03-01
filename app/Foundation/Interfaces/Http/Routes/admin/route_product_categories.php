<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Foundation\Interfaces\Http\Controllers\Admin\Product\ProductCategoryController;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('product-categories', [ProductCategoryController::class, 'categoryList']);
    Route::get('product-categories/{id}', [ProductCategoryController::class, 'show']);
});
