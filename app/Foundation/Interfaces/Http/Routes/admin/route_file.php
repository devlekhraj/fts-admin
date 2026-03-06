<?php

declare(strict_types=1);


use App\Foundation\Interfaces\Http\Controllers\Admin\File\FileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::post('file-upload', [FileController::class, 'fileUpload'])->name('admin.files.upload');
    Route::get('file-list', [FileController::class, 'fileList'])->name('admin.files.list');
    Route::get('file-list-with-usages', [FileController::class, 'fileListWithUsages'])->name('admin.files.list.withUsages');
});
