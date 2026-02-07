<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Repositories;

use App\Foundation\Domain\Emi\Entities\EmiApplication;
use App\Foundation\Domain\Emi\Repositories\EmiApplicationRepository;

class EloquentEmiApplicationRepository implements EmiApplicationRepository
{
    public function save(EmiApplication $entity): void
    {
        // TODO: Persist entity using Eloquent.
    }

    public function findById(string $id): ?EmiApplication
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
