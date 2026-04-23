<?php

declare(strict_types=1);

namespace App\Domains\ProductBrand\Actions;

use App\Domains\ProductBrand\DTOs\BrandUpdateData;
use App\Domains\ProductBrand\Models\ProductBrand;

final class BrandUpdateAction
{
    public function execute(ProductBrand $brand, BrandUpdateData $data): ProductBrand
    {
        $brand->update($data->attributes);

        return $brand->refresh();
    }
}
