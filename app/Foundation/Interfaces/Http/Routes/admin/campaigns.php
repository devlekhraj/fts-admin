<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Foundation\Interfaces\Http\Controllers\Admin\CampaignsController;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('campaigns', [CampaignsController::class, 'index']);
    Route::get('campaigns/{id}', [CampaignsController::class, 'show']);
    Route::post('campaigns', [CampaignsController::class, 'store']);
    Route::put('campaigns/{id}', [CampaignsController::class, 'update']);
    Route::delete('campaigns/{id}', [CampaignsController::class, 'destroy']);
});
