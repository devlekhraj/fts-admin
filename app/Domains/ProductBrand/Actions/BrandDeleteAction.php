<?php

declare(strict_types=1);

namespace App\Domains\ProductBrand\Actions;

use App\Domains\ProductBrand\Models\ProductBrand;

final class BrandDeleteAction
{
    public function execute(ProductBrand $brand): void
    {
        $brand->delete();
    }
}
