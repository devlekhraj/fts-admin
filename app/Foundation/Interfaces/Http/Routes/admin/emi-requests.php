<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Foundation\Interfaces\Http\Controllers\Admin\EmiRequestsController;
use App\Foundation\Interfaces\Http\Controllers\Admin\EmiApplicationController;
use App\Foundation\Interfaces\Http\Controllers\Admin\EmiApplicationsController;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('emi-requests', [EmiRequestsController::class, 'index'])->name('admin.emi-requests.index');
    Route::get('emi-requests/{id}', [EmiRequestsController::class, 'show'])->name('admin.emi-requests.show');
    Route::post('emi-requests', [EmiRequestsController::class, 'store'])->name('admin.emi-requests.store');
    Route::put('emi-requests/{id}', [EmiRequestsController::class, 'update'])->name('admin.emi-requests.update');
    Route::post('emi-requests/{id}/approve', [EmiRequestsController::class, 'approve'])->name('admin.emi-requests.approve');

    Route::post('emi-requests/{id}/generate-application', [EmiApplicationController::class, 'generateApplication'])->name('admin.emi-requests.generate-application');
    Route::get('emi-requests/{id}/application-list', [EmiApplicationsController::class, 'applicationList'])->name('admin.emi-requests.application-list');
    Route::post('emi-requests/{id}/application-pdf', [EmiApplicationsController::class, 'generatePdf']);
});
