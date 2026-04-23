<?php

declare(strict_types=1);

namespace App\Domains\BlogCategory\Services;

use App\Domains\BlogCategory\Actions\BlogCategoryImageDeleteAction;
use App\Domains\BlogCategory\Actions\BlogCategoryImageUpdateAction;
use App\Domains\BlogCategory\DTOs\BlogCategoryImageUpdateData;
use App\Domains\File\Models\FileUsage;

final class BlogCategoryImageService
{
    public function __construct(
        private readonly BlogCategoryImageUpdateAction $blogCategoryImageUpdateAction,
        private readonly BlogCategoryImageDeleteAction $blogCategoryImageDeleteAction,
    ) {}

    public function update(string $categoryId, string $fileUsageId, BlogCategoryImageUpdateData $data): ?FileUsage
    {
        return $this->blogCategoryImageUpdateAction->execute($categoryId, $fileUsageId, $data);
    }

    public function delete(string $categoryId, string $fileUsageId): bool
    {
        return $this->blogCategoryImageDeleteAction->execute($categoryId, $fileUsageId);
    }
}

