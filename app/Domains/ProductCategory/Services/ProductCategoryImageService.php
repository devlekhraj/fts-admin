<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\Services;

use App\Domains\ProductCategory\Actions\CategoryImageDeleteAction;
use App\Domains\ProductCategory\Actions\CategoryImageUpdateAction;
use App\Domains\ProductCategory\DTOs\CategoryImageUpdateData;
use App\Domains\File\Models\FileUsage;

final class ProductCategoryImageService
{
    public function __construct(
        private readonly CategoryImageUpdateAction $categoryImageUpdateAction,
        private readonly CategoryImageDeleteAction $categoryImageDeleteAction,
    ) {}

    public function update(string $categoryId, string $fileUsageId, CategoryImageUpdateData $data): ?FileUsage
    {
        return $this->categoryImageUpdateAction->execute($categoryId, $fileUsageId, $data);
    }

    public function delete(string $categoryId, string $fileUsageId): bool
    {
        return $this->categoryImageDeleteAction->execute($categoryId, $fileUsageId);
    }
}

