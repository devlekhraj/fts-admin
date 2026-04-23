<?php

declare(strict_types=1);

namespace App\Domains\BlogCategory\Actions;

use App\Domains\BlogCategory\DTOs\BlogCategoryCreateData;
use App\Domains\BlogCategory\Models\BlogCategory;

final class BlogCategoryCreateAction
{
    public function execute(BlogCategoryCreateData $data): BlogCategory
    {
        return BlogCategory::query()->create([
            'title' => $data->title,
            'slug' => $data->slug,
            'short_desc' => '',
            'content' => '',
            'status' => $data->status,
        ]);
    }
}

