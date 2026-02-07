<?php

declare(strict_types=1);

namespace App\Foundation\Domain\Brand\Repositories;

use App\Foundation\Domain\Brand\Entities\Brand;

interface BrandRepository
{
    public function save(Brand $entity): void;

    public function findById(string $id): ?Brand;

    public function paginate(int $perPage, int $page = 1): array;

    public function deleteById(string $id): void;
}
