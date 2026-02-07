<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Repositories;

use App\Foundation\Domain\Product\Entities\Product;
use App\Foundation\Domain\Product\Repositories\ProductRepository;

class EloquentProductRepository implements App\Foundation\Domain\Product\Repositories\ProductRepository
{
    public function save(App\Foundation\Domain\Product\Entities\Product $entity): void
    {
        // TODO: Persist entity using Eloquent.
    }

    public function findById(string $id): ?App\Foundation\Domain\Product\Entities\Product
    {
        // TODO: Retrieve entity by ID using Eloquent.
        return null;
    }

    public function paginate(int $perPage, int $page = 1): array
    {
        // TODO: Return paginated results.
        return [];
    }

    public function deleteById(string $id): void
    {
        // TODO: Delete entity by ID using Eloquent.
    }
}
