<?php

declare(strict_types=1);


use App\Foundation\Interfaces\Http\Controllers\Admin\Product\ProductAttributeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin_api'])->prefix('admin')->group(function () {
    Route::get('attribute-list', [ProductAttributeController::class, 'attributeList'])->name('admin.attribute.list');
    Route::get('attributes/{id}/detail', [ProductAttributeController::class, 'attributeDetail'])->name('admin.attribute.detail');
    Route::patch('attributes/{id}/items/{attributeId}', [ProductAttributeController::class, 'updateAttributeItem'])->name('admin.attribute.item.update');
    Route::patch('attributes/{id}/items/{attributeId}/values', [ProductAttributeController::class, 'updateAttributeValues'])->name('admin.attribute.values.update');
});
