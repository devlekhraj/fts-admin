<?php

declare(strict_types=1);

namespace App\Domains\BlogCategory\Actions;

use App\Domains\BlogCategory\Models\BlogCategory;

final class BlogCategoryDeleteAction
{
    public function execute(BlogCategory $category): void
    {
        try {
            $category->files()->detach();
        } catch (\Throwable $e) {
            // Best effort cleanup; proceed to delete category even if detach fails.
        }

        $category->delete();
    }
}

