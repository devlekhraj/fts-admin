<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Foundation\Interfaces\Http\Controllers\Admin\RbacController;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('rbac/roles', [RbacController::class, 'roles']);
    Route::get('rbac/permissions', [RbacController::class, 'permissions']);
});
