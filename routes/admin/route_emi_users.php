<?php

declare(strict_types=1);

use App\Domains\EmiUser\Controllers\EmiUserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('emi-users', [EmiUserController::class, 'index']);
});
