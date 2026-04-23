<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\Actions;

use App\Domains\ProductCategory\DTOs\CategoryCreateData;
use App\Domains\ProductCategory\Models\ProductCategory;

final class CategoryCreateAction
{
    public function execute(CategoryCreateData $data): ProductCategory
    {
        return ProductCategory::query()->create([
            'title' => $data->title,
            'slug' => $data->slug,
            'status' => $data->status,
        ]);
    }
}
