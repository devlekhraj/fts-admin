<?php

declare(strict_types=1);

namespace App\Domains\Blog\Actions;

use App\Domains\Blog\DTOs\BlogCreateData;
use App\Domains\Blog\Models\Blog;
use App\Domains\BlogCategory\Models\BlogCategory;

final class BlogCreateAction
{
    public function execute(BlogCreateData $data): Blog
    {
        // Ensure default "Unlisted" category exists (restoring if soft-deleted).
        $category = BlogCategory::query()
            ->withTrashed()
            ->where('slug', 'unlisted')
            ->first();

        if ($category?->trashed()) {
            $category->restore();
        }

        if (! $category) {
            $category = BlogCategory::query()->create([
                'title' => 'Unlisted',
                'slug' => 'unlisted',
                'short_desc' => '',
                'content' => '',
                'status' => true,
            ]);
        }

        return Blog::query()->create([
            'title' => $data->title,
            'slug' => $data->slug,
            'status' => $data->status,
            'category_id' => $category->id,
            'content' => '',
            'author' => '',
        ]);
    }
}

