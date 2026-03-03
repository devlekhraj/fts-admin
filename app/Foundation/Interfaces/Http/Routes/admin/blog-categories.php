<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\Blog\BlogCategoriesController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('blog-categories', [BlogCategoriesController::class, 'categoryList'])->name('admin.blog-categories.list');
    Route::get('blog-categories/{id}', [BlogCategoriesController::class, 'categoryShow'])->name('admin.blog-categories.show');
    Route::post('blog-categories', [BlogCategoriesController::class, 'store'])->name('admin.blog-categories.store');
    Route::put('blog-categories/{id}', [BlogCategoriesController::class, 'update'])->name('admin.blog-categories.update');
    Route::delete('blog-categories/{id}', [BlogCategoriesController::class, 'destroy'])->name('admin.blog-categories.destroy');
});
