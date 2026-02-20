<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\AdminUserController;
use App\Foundation\Interfaces\Http\Controllers\Admin\AdminUserUpdateController;
use App\Foundation\Interfaces\Http\Controllers\Admin\UsersController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('admin-list', [UsersController::class, 'adminList'])->name('admin.users.admin-list');
    Route::post('admin-create', [AdminUserController::class, 'saveAdmin'])->name('admin.users.admin-create');

    // update routes
    Route::put('admin-users/{id}/basic-info', [AdminUserUpdateController::class, 'updateBasic'])->name('admin.users.update-basic-info');
    Route::put('admin-users/{id}/password', [AdminUserUpdateController::class, 'updatePassword'])->name('admin.users.update-password');
    Route::post('admin-users/{id}/email/verification-code', [AdminUserUpdateController::class, 'sendEmailVerificationCode'])->name('admin.users.email-send-code');
    Route::put('admin-users/{id}/email', [AdminUserUpdateController::class, 'updateEmail'])->name('admin.users.update-email');
    Route::delete('admin-users/{id}/delete', [AdminUserUpdateController::class, 'delete'])->name('admin.users.delete');

});
