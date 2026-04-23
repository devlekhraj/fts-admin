<?php

declare(strict_types=1);

use App\Domains\Rbac\Controllers\RbacController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('rbac/roles', [RbacController::class, 'indexRoles']);
    Route::get('rbac/permissions', [RbacController::class, 'indexPermissions']);
});
