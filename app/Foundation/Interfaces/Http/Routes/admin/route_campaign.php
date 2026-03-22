<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\Campaign\AdminCampaignController;
use Illuminate\Support\Facades\Route;



// TODO: Protect with admin auth + permission middleware.

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    // Route::get('products', [ProductsController::class, 'productList'])->name('admin.products.index');

     Route::get('campaigns', [AdminCampaignController::class, 'index']);
    Route::post('campaigns', [AdminCampaignController::class, 'storeUpdate']);

    Route::get('campaigns/{id}', [AdminCampaignController::class, 'show']);
    Route::post('campaigns/{id}/assign-products', [AdminCampaignController::class, 'assignProducts']);
    Route::get('campaigns/{id}/products', [AdminCampaignController::class, 'getCampaignProducts']);
    Route::put('campaigns/{id}/update-discount', [AdminCampaignController::class, 'updateCampaignDiscount']);

    Route::put('campaign-products/{id}/update', [AdminCampaignController::class, 'updateCampaignProduct']);
    Route::delete('campaign-products/{id}/remove', [AdminCampaignController::class, 'removeCampaignProduct']);

    Route::patch('campaigns/{id}/toggle-published', [AdminCampaignController::class, 'togglePublished']);
    Route::delete('campaigns/{id}/delete', [AdminCampaignController::class, 'delete']);

    Route::post('campaigns/{id}/images', [AdminCampaignController::class, 'storeImage']);

});
