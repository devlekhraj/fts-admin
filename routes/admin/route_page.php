<?php

declare(strict_types=1);

use App\Domains\Page\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('pages', [PageController::class, 'index'])->name('admin.pages.list');
    Route::get('pages/{id}', [PageController::class, 'show'])->name('admin.pages.show');
    Route::post('pages', [PageController::class, 'store'])->name('admin.pages.store');
    Route::put('pages/{id}', [PageController::class, 'update'])->name('admin.pages.update');
    Route::delete('pages/{id}', [PageController::class, 'destroy'])->name('admin.pages.delete');
});
