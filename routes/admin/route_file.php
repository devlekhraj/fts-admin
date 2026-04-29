<?php

declare(strict_types=1);

use App\Domains\File\Controllers\FileController;
use App\Domains\File\Controllers\FileUsageController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {

    Route::post('file-upload', [FileController::class, 'upload'])->name('admin.files.upload');

    Route::post('file-assign', [FileController::class, 'assign'])->name('admin.files.assign');

    Route::get('file-list', [FileController::class, 'index'])->name('admin.files.list');

    Route::get('file-list-with-usages', [FileController::class, 'indexWithUsages'])->name('admin.files.list.withUsages');

    Route::get('file-usages', [FileUsageController::class, 'index'])->name('admin.files.usage.index');


    // file usages routes
    Route::put('file-usage/{fileUsageId}', [FileUsageController::class, 'update'])->name('admin.files.usage.update');
    Route::delete('file-usage/{fileUsageId}', [FileUsageController::class, 'destroy'])->name('admin.files.usage.delete');
});
