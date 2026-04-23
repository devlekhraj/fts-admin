<?php

declare(strict_types=1);

namespace App\Domains\BlogCategory\Actions;

use App\Domains\BlogCategory\DTOs\BlogCategoryUpdateData;
use App\Domains\BlogCategory\Models\BlogCategory;

final class BlogCategoryUpdateAction
{
    public function execute(BlogCategory $category, BlogCategoryUpdateData $data): BlogCategory
    {
        $category->update($data->attributes);

        return $category->refresh();
    }
}

