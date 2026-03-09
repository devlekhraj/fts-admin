<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\Developer\DeveloperController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('developer/api-keys', [DeveloperController::class, 'index'])->name('admin.developer.keylist');
    Route::post('developer/api-keys', [DeveloperController::class, 'store'])->name('admin.developer.keystore');
    Route::delete('developer/api-keys/{id}', [DeveloperController::class, 'destroy'])->name('admin.developer.keydelete');
});
