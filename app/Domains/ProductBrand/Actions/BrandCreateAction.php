<?php

declare(strict_types=1);

namespace App\Domains\ProductBrand\Actions;

use App\Domains\ProductBrand\DTOs\BrandCreateData;
use App\Domains\ProductBrand\Models\ProductBrand;

final class BrandCreateAction
{
    public function execute(BrandCreateData $data): ProductBrand
    {
        $nextSeqNo = (int) (ProductBrand::query()->max('seq_no') ?? 0) + 1;

        return ProductBrand::query()->create([
            'name' => $data->name,
            'slug' => $data->slug,
            'status' => $data->status,
            'seq_no' => $nextSeqNo,
        ]);
    }
}
