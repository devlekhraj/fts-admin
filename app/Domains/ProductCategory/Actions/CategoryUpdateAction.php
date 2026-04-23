<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\Actions;

use App\Domains\ProductCategory\DTOs\CategoryUpdateData;
use App\Domains\ProductCategory\Models\ProductCategory;

final class CategoryUpdateAction
{
    public function execute(ProductCategory $category, CategoryUpdateData $data): ProductCategory
    {
        $category->update($data->attributes);

        return $category->refresh();
    }
}
