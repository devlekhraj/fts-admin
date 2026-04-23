<?php

declare(strict_types=1);

namespace App\Domains\ProductBrand\Actions;

use App\Domains\ProductBrand\Models\ProductBrand;

final class BrandDetailAction
{
    public function execute(string $id): ProductBrand
    {
        return ProductBrand::query()
            ->with(['defaultFile', 'files'])
            ->withCount('products')
            ->findOrFail($id);
    }
}
