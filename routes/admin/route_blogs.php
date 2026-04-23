<?php

declare(strict_types=1);


// blog categories
use App\Domains\BlogCategory\Controllers\BlogCategoryController;
use App\Domains\BlogCategory\Controllers\BlogCategoryImageController;

// blogs
use App\Domains\Blog\Controllers\BlogController;
use App\Domains\Blog\Controllers\BlogImageController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('blog-categories', [BlogCategoryController::class, 'index'])->name('admin.blog-categories.list');
    Route::get('blog-categories/{id}', [BlogCategoryController::class, 'show'])->name('admin.blog-categories.show');
    Route::post('blog-categories', [BlogCategoryController::class, 'store'])->name('admin.blog-categories.store');
    Route::put('blog-categories/{id}', [BlogCategoryController::class, 'update'])->name('admin.blog-categories.update');
    Route::delete('blog-categories/{id}', [BlogCategoryController::class, 'destroy'])->name('admin.blog-categories.destroy');
    Route::put('blog-categories/{id}/images/{fileUsageId}', [BlogCategoryImageController::class, 'update'])->name('admin.blog-categories.images.update');
    Route::delete('blog-categories/{id}/images/{fileUsageId}', [BlogCategoryImageController::class, 'delete'])->name('admin.blog-categories.images.delete');
});


Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('blogs', [BlogController::class, 'index'])->name('admin.blogs.index');
    Route::get('blogs/{id}', [BlogController::class, 'show'])->name('admin.blogs.show');
    Route::post('blogs', [BlogController::class, 'store'])->name('admin.blogs.store');
    Route::put('blogs/{id}', [BlogController::class, 'update'])->name('admin.blogs.update');
    Route::delete('blogs/{id}', [BlogController::class, 'destroy'])->name('admin.blogs.destroy');
    Route::put('blogs/{id}/images/{fileUsageId}', [BlogImageController::class, 'update'])->name('admin.blogs.images.update');
    Route::delete('blogs/{id}/images/{fileUsageId}', [BlogImageController::class, 'delete'])->name('admin.blogs.images.delete');
});
