<?php

declare(strict_types=1);

namespace App\Domains\ProductBrand\Services;

use App\Domains\ProductBrand\Actions\BrandCreateAction;
use App\Domains\ProductBrand\Actions\BrandDeleteAction;
use App\Domains\ProductBrand\Actions\BrandDetailAction;
use App\Domains\ProductBrand\Actions\BrandListAction;
use App\Domains\ProductBrand\Actions\BrandUpdateAction;
use App\Domains\ProductBrand\DTOs\BrandCreateData;
use App\Domains\ProductBrand\DTOs\BrandUpdateData;
use App\Domains\ProductBrand\Models\ProductBrand;

final class ProductBrandService
{
    public function __construct(
        private readonly BrandListAction $brandListAction,
        private readonly BrandDetailAction $brandDetailAction,
        private readonly BrandCreateAction $brandCreateAction,
        private readonly BrandUpdateAction $brandUpdateAction,
        private readonly BrandDeleteAction $brandDeleteAction,
    ) {}

    public function list(?string $search, int $perPageParam): array
    {
        return $this->brandListAction->execute($search, $perPageParam);
    }

    public function detail(string $id): ProductBrand
    {
        return $this->brandDetailAction->execute($id);
    }

    public function create(BrandCreateData $data): ProductBrand
    {
        return $this->brandCreateAction->execute($data);
    }

    public function update(string $id, BrandUpdateData $data): ProductBrand
    {
        $brand = ProductBrand::query()->findOrFail($id);

        return $this->brandUpdateAction->execute($brand, $data);
    }

    public function delete(string $id): void
    {
        $brand = ProductBrand::query()->findOrFail($id);

        $this->brandDeleteAction->execute($brand);
    }
}
