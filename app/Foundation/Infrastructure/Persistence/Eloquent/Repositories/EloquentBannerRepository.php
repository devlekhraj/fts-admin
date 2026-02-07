<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Repositories;

use App\Foundation\Domain\Banner\Entities\Banner;
use App\Foundation\Domain\Banner\Repositories\BannerRepository;

class EloquentBannerRepository implements App\Foundation\Domain\Banner\Repositories\BannerRepository
{
    public function save(App\Foundation\Domain\Banner\Entities\Banner $entity): void
    {
        // TODO: Persist entity using Eloquent.
    }

    public function findById(string $id): ?App\Foundation\Domain\Banner\Entities\Banner
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
