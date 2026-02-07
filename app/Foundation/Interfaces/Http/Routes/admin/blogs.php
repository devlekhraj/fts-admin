<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Foundation\Interfaces\Http\Controllers\Admin\BlogsController;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('blogs', [BlogsController::class, 'index']);
    Route::get('blogs/{id}', [BlogsController::class, 'show']);
    Route::post('blogs', [BlogsController::class, 'store']);
    Route::put('blogs/{id}', [BlogsController::class, 'update']);
    Route::delete('blogs/{id}', [BlogsController::class, 'destroy']);
});
