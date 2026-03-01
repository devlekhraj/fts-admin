<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\Blog\BlogCategoriesController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('blog-categories', [BlogCategoriesController::class, 'categoryList']);
    Route::get('blog-categories/{id}', [BlogCategoriesController::class, 'show']);
    Route::post('blog-categories', [BlogCategoriesController::class, 'store']);
    Route::put('blog-categories/{id}', [BlogCategoriesController::class, 'update']);
    Route::delete('blog-categories/{id}', [BlogCategoriesController::class, 'destroy']);
});
