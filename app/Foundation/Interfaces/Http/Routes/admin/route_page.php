<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\WebPageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('pages', [WebPageController::class, 'list'])->name('admin.pages.list');
    Route::get('pages/{id}', [WebPageController::class, 'show'])->name('admin.pages.show');
    Route::delete('pages/{id}', [WebPageController::class, 'destroy'])->name('admin.pages.delete');
});
