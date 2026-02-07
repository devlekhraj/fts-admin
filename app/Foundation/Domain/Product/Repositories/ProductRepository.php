<?php

declare(strict_types=1);

namespace App\Foundation\Domain\Product\Repositories;

use App\Foundation\Domain\Product\Entities\Product;

interface ProductRepository
{
    public function save(Product $entity): void;

    public function findById(string $id): ?Product;

    public function paginate(int $perPage, int $page = 1): array;

    public function deleteById(string $id): void;
}
