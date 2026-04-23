<?php

declare(strict_types=1);

namespace App\Domains\Blog\Actions;

use App\Domains\Blog\Models\Blog;
use App\Domains\File\Models\FileUsage;

final class BlogDeleteAction
{
    public function execute(Blog $blog): void
    {
        try {
            FileUsage::query()
                ->where('usage_type', 'blogs')
                ->where('usage_id', $blog->id)
                ->delete();
            $blog->files()->detach();
        } catch (\Throwable $e) {
            // proceed even if cleanup partially fails
        }

        $blog->delete();
    }
}

