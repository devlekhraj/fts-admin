<?php

declare(strict_types=1);

use App\Domains\Campaign\Controllers\CampaignController;
use App\Domains\Campaign\Controllers\CampaignImageController;
use App\Domains\Campaign\Controllers\CampaignProductController;
use Illuminate\Support\Facades\Route;



// TODO: Protect with admin auth + permission middleware.

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('campaigns', [CampaignController::class, 'index']);
    Route::post('campaigns', [CampaignController::class, 'store']);
    Route::put('campaigns/{id}/update', [CampaignController::class, 'update']);
    Route::get('campaigns/{id}', [CampaignController::class, 'show']);

    Route::get('campaigns/{id}/products', [CampaignProductController::class, 'index']);
    Route::post('campaigns/{id}/assign-products', [CampaignProductController::class, 'store']);
    Route::put('campaigns/{id}/update-discount', [CampaignProductController::class, 'updateDiscount']);

    Route::put('campaign-products/{id}/update', [CampaignProductController::class, 'update']);
    Route::delete('campaign-products/{id}/remove', [CampaignProductController::class, 'destroy']);

    Route::patch('campaigns/{id}/toggle-published', [CampaignController::class, 'togglePublished']);
    Route::delete('campaigns/{id}/delete', [CampaignController::class, 'destroy']);

    Route::post('campaigns/{id}/images', [CampaignImageController::class, 'store']);
});
