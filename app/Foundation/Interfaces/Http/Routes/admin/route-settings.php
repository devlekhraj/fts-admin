<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\SettingShowController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('settings', [SettingShowController::class, 'index']);
    Route::get('settings/{id}/details', [SettingShowController::class, 'details']);
});
