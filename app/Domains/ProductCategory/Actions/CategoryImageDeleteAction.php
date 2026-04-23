<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\Actions;

use App\Domains\File\Models\FileUsage;

final class CategoryImageDeleteAction
{
    public function execute(string $categoryId, string $fileUsageId): bool
    {
        $fileUsage = FileUsage::query()
            ->where('id', $fileUsageId)
            ->where('usage_type', 'product_categories')
            ->where('usage_id', $categoryId)
            ->first();

        if (! $fileUsage) {
            return false;
        }

        $fileUsage->delete();

        return true;
    }
}

