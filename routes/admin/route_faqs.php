<?php

declare(strict_types=1);

use App\Domains\Faq\Controllers\FaqController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('faqs', [FaqController::class, 'index'])->name('admin.faqs.list');
});
