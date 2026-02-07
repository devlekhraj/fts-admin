<?php

declare(strict_types=1);

namespace App\Foundation\Domain\CatalogCategory\Repositories;

use App\Foundation\Domain\CatalogCategory\Entities\CatalogCategory;

interface CatalogCategoryRepository
{
    public function save(CatalogCategory $entity): void;

    public function findById(string $id): ?CatalogCategory;

    public function paginate(int $perPage, int $page = 1): array;

    public function deleteById(string $id): void;
}
