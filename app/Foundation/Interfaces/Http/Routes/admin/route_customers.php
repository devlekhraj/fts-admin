<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\CustomerController;

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('customer-list', [CustomerController::class, 'customerList'])->name('admin.customers.list');
    Route::get('customers/{id}', [CustomerController::class, 'customerDetail'])->name('admin.customers.detail');

    Route::post('customer-save', [CustomerController::class, 'saveCustomer'])->name('admin.customer-create');

    // update routes
    Route::put('customers/{id}/basic-info', [CustomerController::class, 'updateBasic'])->name('admin.customer-update-basic-info');
    Route::put('customers/{id}/password', [CustomerController::class, 'updatePassword'])->name('admin.customer-update-password');
    Route::post('customers/{id}/email/verification-code', [CustomerController::class, 'sendEmailVerificationCode'])->name('admin.customer-email-send-code');
    Route::put('customers/{id}/email', [CustomerController::class, 'updateEmail'])->name('admin.customer-update-email');
    Route::delete('customers/{id}/delete', [CustomerController::class, 'delete'])->name('admin.customer-delete');

});
