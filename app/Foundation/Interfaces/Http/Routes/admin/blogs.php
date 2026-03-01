<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\Blog\BlogsController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('blogs', [BlogsController::class, 'blogList']);
    Route::get('blogs/{id}', [BlogsController::class, 'blogShow']);
    Route::post('blogs', [BlogsController::class, 'blogStore']);
    Route::put('blogs/{id}', [BlogsController::class, 'blogUpdate']);
    Route::delete('blogs/{id}', [BlogsController::class, 'blogDestroy']);
});
