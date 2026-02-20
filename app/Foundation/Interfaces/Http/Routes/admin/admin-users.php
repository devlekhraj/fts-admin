<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\AdminUserController;
use App\Foundation\Interfaces\Http\Controllers\Admin\UsersController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('admin-list', [UsersController::class, 'adminList'])->name('admin.users.admin-list');
    Route::post('admin-create', [AdminUserController::class, 'saveAdmin'])->name('admin.users.admin-create');
});
