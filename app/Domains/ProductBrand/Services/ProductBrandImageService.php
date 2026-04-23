<?php

declare(strict_types=1);

namespace App\Domains\ProductBrand\Services;

use App\Domains\ProductBrand\Actions\BrandImageDeleteAction;
use App\Domains\ProductBrand\Actions\BrandImageUpdateAction;
use App\Domains\ProductBrand\DTOs\BrandImageUpdateData;
use App\Domains\File\Models\FileUsage;

final class ProductBrandImageService
{
    public function __construct(
        private readonly BrandImageUpdateAction $brandImageUpdateAction,
        private readonly BrandImageDeleteAction $brandImageDeleteAction,
    ) {}

    public function update(string $brandId, string $fileUsageId, BrandImageUpdateData $data): ?FileUsage
    {
        return $this->brandImageUpdateAction->execute($brandId, $fileUsageId, $data);
    }

    public function delete(string $brandId, string $fileUsageId): bool
    {
        return $this->brandImageDeleteAction->execute($brandId, $fileUsageId);
    }
}

