<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Persistence\Eloquent\Repositories;

use App\Foundation\Domain\Emi\Entities\EmiUser;
use App\Foundation\Domain\Emi\Repositories\EmiUserRepository;

class EloquentEmiUserRepository implements EmiUserRepository
{
    public function save(EmiUser $entity): void
    {
        // TODO: Persist entity using Eloquent.
    }

    public function findById(string $id): ?EmiUser
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
