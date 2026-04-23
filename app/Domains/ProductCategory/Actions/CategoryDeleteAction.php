<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\Actions;

use App\Domains\ProductCategory\Models\ProductCategory;

final class CategoryDeleteAction
{
    public function execute(ProductCategory $category): void
    {
        $category->delete();
    }
}
