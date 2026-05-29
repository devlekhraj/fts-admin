<?php

declare(strict_types=1);

use App\Domains\Admin\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('dashboard/metrics', [DashboardController::class, 'metrics'])->name('admin.dashboard.metrics');
    Route::get('dashboard/latest', [DashboardController::class, 'latest'])->name('admin.dashboard.latest');
});
