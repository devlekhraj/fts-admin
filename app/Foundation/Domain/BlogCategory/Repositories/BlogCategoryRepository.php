<?php

declare(strict_types=1);

namespace App\Foundation\Domain\BlogCategory\Repositories;

use App\Foundation\Domain\BlogCategory\Entities\BlogCategory;

interface BlogCategoryRepository
{
    public function save(BlogCategory $entity): void;

    public function findById(string $id): ?BlogCategory;

    public function paginate(int $perPage, int $page = 1): array;

    public function deleteById(string $id): void;
}
