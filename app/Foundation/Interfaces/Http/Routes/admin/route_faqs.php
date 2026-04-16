<?php

declare(strict_types=1);

use App\Foundation\Interfaces\Http\Controllers\Admin\FaqController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('faqs', [FaqController::class, 'list'])->name('admin.faqs.list');
});

