<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Foundation\Interfaces\Http\Controllers\Admin\BannersController;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('banners', [BannersController::class, 'index']);
    Route::get('banners/{id}', [BannersController::class, 'show']);
    Route::post('banners', [BannersController::class, 'store']);
    Route::put('banners/{id}', [BannersController::class, 'update']);
    Route::delete('banners/{id}', [BannersController::class, 'destroy']);
});
