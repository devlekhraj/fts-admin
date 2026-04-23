<?php

declare(strict_types=1);

namespace App\Domains\Blog\Services;

use App\Domains\Blog\Actions\BlogImageDeleteAction;
use App\Domains\Blog\Actions\BlogImageUpdateAction;
use App\Domains\Blog\DTOs\BlogImageUpdateData;
use App\Domains\File\Models\FileUsage;

final class BlogImageService
{
    public function __construct(
        private readonly BlogImageUpdateAction $blogImageUpdateAction,
        private readonly BlogImageDeleteAction $blogImageDeleteAction,
    ) {}

    public function update(string $blogId, string $fileUsageId, BlogImageUpdateData $data): ?FileUsage
    {
        return $this->blogImageUpdateAction->execute($blogId, $fileUsageId, $data);
    }

    public function delete(string $blogId, string $fileUsageId): bool
    {
        return $this->blogImageDeleteAction->execute($blogId, $fileUsageId);
    }
}

