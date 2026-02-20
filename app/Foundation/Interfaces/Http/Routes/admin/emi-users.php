<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\EmiUsersController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('emi-users', [EmiUsersController::class, 'index']);
});
