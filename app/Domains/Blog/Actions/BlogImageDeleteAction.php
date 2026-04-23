<?php

declare(strict_types=1);

namespace App\Domains\Blog\Actions;

use App\Domains\File\Models\FileUsage;

final class BlogImageDeleteAction
{
    public function execute(string $blogId, string $fileUsageId): bool
    {
        $fileUsage = FileUsage::query()
            ->where('id', $fileUsageId)
            ->where('usage_type', 'blogs')
            ->where('usage_id', $blogId)
            ->first();

        if (! $fileUsage) {
            return false;
        }

        $fileUsage->delete();

        return true;
    }
}

