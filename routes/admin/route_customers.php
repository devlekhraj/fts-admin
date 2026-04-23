<?php

declare(strict_types=1);

use App\Domains\Customer\Controllers\CustomerController;

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('customer-list', [CustomerController::class, 'index'])->name('admin.customers.list');
    Route::get('customers/{id}', [CustomerController::class, 'show'])->name('admin.customers.detail');
});
