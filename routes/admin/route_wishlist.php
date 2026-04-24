<?php

declare(strict_types=1);


use Illuminate\Support\Facades\Route;
use App\Domains\Wishlist\Controllers\WishlistController;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('wishlists', [WishlistController::class, 'index'])->name('admin.wishlist.list');
});
