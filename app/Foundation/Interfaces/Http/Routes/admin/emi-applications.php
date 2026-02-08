<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\EmiApplicationsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('emi-applications', [EmiApplicationsController::class, 'index']);
    Route::get('emi-applications/{id}', [EmiApplicationsController::class, 'show']);
    Route::post('emi-applications', [EmiApplicationsController::class, 'store']);
    Route::put('emi-applications/{id}', [EmiApplicationsController::class, 'update']);
    Route::post('emi-applications/{id}/approve', [EmiApplicationsController::class, 'approve']);
});
