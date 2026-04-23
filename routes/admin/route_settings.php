<?php

declare(strict_types=1);

use App\Domains\Setting\Controllers\LogoController;
use App\Domains\Setting\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('settings', [SettingController::class, 'index']);
    Route::get('settings/{id}/details', [SettingController::class, 'show']);
    Route::put('settings/{module}', [SettingController::class, 'update']);
    Route::get('logo-images', [LogoController::class, 'index']);
});
