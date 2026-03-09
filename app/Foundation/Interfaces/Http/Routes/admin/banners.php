<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\Banner\BannerController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('banners', [BannerController::class, 'bannerList'])->name('admin.banners.list');
    Route::get('banners/{id}', [BannerController::class, 'show'])->name('admin.banners.show');
    Route::post('banners', [BannerController::class, 'store'])->name('admin.banners.store');
    Route::put('banners/{id}', [BannerController::class, 'update'])->name('admin.banners.update');
    Route::delete('banners/{id}', [BannerController::class, 'destroy'])->name('admin.banners.destroy');
});
