<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\Actions;

use App\Domains\ProductCategory\Models\ProductCategory;

final class CategoryDetailAction
{
    public function execute(string $id): ProductCategory
    {
        return ProductCategory::query()
            ->with(['defaultFile', 'files'])
            ->findOrFail($id);
    }
}
