<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Foundation\Interfaces\Http\Controllers\Admin\EmiBankController;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('emi-banks', [EmiBankController::class, 'index']);
    Route::get('emi-banks/{id}/tenures', [EmiBankController::class, 'tenureList']);
    Route::get('emi-banks/{id}', [EmiBankController::class, 'show']);
});
