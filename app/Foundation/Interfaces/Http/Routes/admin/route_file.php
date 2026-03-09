<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\File\FileController;
use App\Foundation\Interfaces\Http\Controllers\Admin\File\FileUsageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::post('file-assign', [FileController::class, 'fileAssign'])->name('admin.files.assign');
    Route::get('file-list', [FileController::class, 'fileList'])->name('admin.files.list');
    Route::get('file-list-with-usages', [FileController::class, 'fileListWithUsages'])->name('admin.files.list.withUsages');

    // file usages routes
    Route::put('file-usage/{fileUsageId}', [FileUsageController::class, 'update'])->name('admin.files.usage.update');
    Route::delete('file-usage/{fileUsageId}', [FileUsageController::class, 'delete'])->name('admin.files.usage.delete');
});
