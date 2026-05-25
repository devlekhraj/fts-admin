<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\EmiRequest\Controllers\EmiRequestController;
use App\Domains\EmiRequest\Controllers\EmiRequestApplicationController;
use App\Domains\EmiRequest\Controllers\EmiApplicationController;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('emi-requests', [EmiRequestController::class, 'index'])->name('admin.emi-requests.index');
    Route::get('emi-requests/{id}', [EmiRequestController::class, 'show'])->name('admin.emi-requests.show');
    Route::post('emi-requests', [EmiRequestController::class, 'store'])->name('admin.emi-requests.store');
    Route::put('emi-requests/{id}', [EmiRequestController::class, 'update'])->name('admin.emi-requests.update');
    Route::post('emi-requests/{id}/approve', [EmiRequestController::class, 'approve'])->name('admin.emi-requests.approve');
    Route::post('emi-requests/{id}/reject', [EmiRequestController::class, 'reject'])->name('admin.emi-requests.reject');
    Route::post('emi-requests/{id}/delete', [EmiRequestController::class, 'delete'])->name('admin.emi-requests.delete');

    Route::post('emi-requests/{id}/generate-application', [EmiRequestApplicationController::class, 'generateApplication'])->name('admin.emi-requests.generate-application');
    Route::get('emi-requests/{id}/application-list', [EmiApplicationController::class, 'index'])->name('admin.emi-requests.application-list');
    Route::post('emi-requests/{id}/application-pdf', [EmiApplicationController::class, 'generatePdf']);

    Route::post('emi-applications/{id}/approve', [EmiApplicationController::class, 'approve'])->name('admin.emi-applications.approve');
});
