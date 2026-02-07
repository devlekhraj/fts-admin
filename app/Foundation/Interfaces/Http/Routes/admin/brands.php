<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Foundation\Interfaces\Http\Controllers\Admin\BrandsController;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('brands', [BrandsController::class, 'index']);
    Route::get('brands/{id}', [BrandsController::class, 'show']);
    Route::post('brands', [BrandsController::class, 'store']);
    Route::put('brands/{id}', [BrandsController::class, 'update']);
    Route::delete('brands/{id}', [BrandsController::class, 'destroy']);
});
