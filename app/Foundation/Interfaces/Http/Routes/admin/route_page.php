<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\WebPageController;
use App\Foundation\Interfaces\Http\Controllers\Admin\WebPageStoreController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('pages', [WebPageController::class, 'list'])->name('admin.pages.list');
    Route::get('pages/{id}', [WebPageController::class, 'show'])->name('admin.pages.show');
    Route::post('pages', [WebPageStoreController::class, 'store'])->name('admin.pages.store');
    Route::put('pages/{id}', [WebPageStoreController::class, 'update'])->name('admin.pages.update');
    Route::delete('pages/{id}', [WebPageController::class, 'destroy'])->name('admin.pages.delete');
});
