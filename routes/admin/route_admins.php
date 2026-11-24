<?php

declare(strict_types=1);

use App\Domains\Admin\Controllers\AccountController;
use App\Domains\Admin\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('admin-list', [AdminController::class, 'index'])->name('admin.users.admin-list');
    Route::post('admin-create', [AdminController::class, 'store'])->name('admin.users.admin-create');

    // your own account: open to every signed-in admin
    Route::put('account/profile', [AccountController::class, 'updateProfile'])->middleware('throttle:10,1')->name('admin.account.profile');
    Route::put('account/password', [AccountController::class, 'updatePassword'])->middleware('throttle:6,1')->name('admin.account.password');

    // update routes
    Route::put('admin-users/{admin}/basic-info', [AdminController::class, 'updateProfile'])->name('admin.users.update-basic-info');
    Route::put('admin-users/{admin}/password', [AdminController::class, 'updatePassword'])->name('admin.users.update-password');
    Route::put('admin-users/{admin}/role', [AdminController::class, 'updateRole'])->name('admin.users.update-role');
    Route::post('admin-users/{admin}/email/verification-code', [AdminController::class, 'sendEmailVerificationCode'])->name('admin.users.email-send-code');
    Route::put('admin-users/{admin}/email', [AdminController::class, 'updateEmail'])->name('admin.users.update-email');
    Route::delete('admin-users/{admin}/delete', [AdminController::class, 'destroy'])->name('admin.users.delete');

});
