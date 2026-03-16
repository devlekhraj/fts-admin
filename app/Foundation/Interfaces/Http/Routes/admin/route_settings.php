<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\SettingShowController;
use App\Foundation\Interfaces\Http\Controllers\Admin\LogoController;
use App\Foundation\Interfaces\Http\Controllers\Admin\SettingUpdateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('settings', [SettingShowController::class, 'index']);
    Route::get('settings/{id}/details', [SettingShowController::class, 'details']);
    Route::put('settings/{module}', [SettingUpdateController::class, 'update']);
    Route::get('logo-images', [LogoController::class, 'index']);
});
