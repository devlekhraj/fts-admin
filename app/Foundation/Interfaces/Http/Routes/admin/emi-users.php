<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Foundation\Interfaces\Http\Controllers\Admin\EmiUsersController;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('emi-users', [EmiUsersController::class, 'index']);
});
