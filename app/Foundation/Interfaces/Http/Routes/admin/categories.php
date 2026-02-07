<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Foundation\Interfaces\Http\Controllers\Admin\CategoriesController;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('categories', [CategoriesController::class, 'index']);
    Route::get('categories/{id}', [CategoriesController::class, 'show']);
    Route::post('categories', [CategoriesController::class, 'store']);
    Route::put('categories/{id}', [CategoriesController::class, 'update']);
    Route::delete('categories/{id}', [CategoriesController::class, 'destroy']);
});
