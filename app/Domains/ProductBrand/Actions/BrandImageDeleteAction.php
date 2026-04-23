<?php

declare(strict_types=1);

namespace App\Domains\ProductBrand\Actions;

use App\Domains\File\Models\FileUsage;

final class BrandImageDeleteAction
{
    public function execute(string $brandId, string $fileUsageId): bool
    {
        $fileUsage = FileUsage::query()
            ->where('id', $fileUsageId)
            ->where('usage_type', 'product_brands')
            ->where('usage_id', $brandId)
            ->first();

        if (! $fileUsage) {
            return false;
        }

        $fileUsage->delete();

        return true;
    }
}

