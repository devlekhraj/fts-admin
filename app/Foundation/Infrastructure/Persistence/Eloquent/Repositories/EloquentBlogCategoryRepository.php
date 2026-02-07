<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Repositories;

use App\Foundation\Domain\BlogCategory\Entities\BlogCategory;
use App\Foundation\Domain\BlogCategory\Repositories\BlogCategoryRepository;

class EloquentBlogCategoryRepository implements App\Foundation\Domain\BlogCategory\Repositories\BlogCategoryRepository
{
    public function save(App\Foundation\Domain\BlogCategory\Entities\BlogCategory $entity): void
    {
        // TODO: Persist entity using Eloquent.
    }

    public function findById(string $id): ?App\Foundation\Domain\BlogCategory\Entities\BlogCategory
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
