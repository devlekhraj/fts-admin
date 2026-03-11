<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\Blog\BlogsController;
use App\Foundation\Interfaces\Http\Controllers\Admin\Blog\BlogImageController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('blogs', [BlogsController::class, 'blogList'])->name('admin.blogs.index');
    Route::get('blogs/{id}', [BlogsController::class, 'blogShow'])->name('admin.blogs.show');
    Route::post('blogs', [BlogsController::class, 'blogStore'])->name('admin.blogs.store');
    Route::put('blogs/{id}', [BlogsController::class, 'blogUpdate'])->name('admin.blogs.update');
    Route::delete('blogs/{id}', [BlogsController::class, 'blogDestroy'])->name('admin.blogs.destroy');
    Route::put('blogs/{id}/images/{fileUsageId}', [BlogImageController::class, 'update'])->name('admin.blogs.images.update');
    Route::delete('blogs/{id}/images/{fileUsageId}', [BlogImageController::class, 'delete'])->name('admin.blogs.images.delete');
});
