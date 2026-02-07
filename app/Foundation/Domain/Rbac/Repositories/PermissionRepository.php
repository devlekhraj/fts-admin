<?php

declare(strict_types=1);

namespace App\Foundation\Domain\Rbac\Repositories;

use App\Foundation\Domain\Rbac\Entities\Permission;

interface PermissionRepository
{
    public function save(Permission $entity): void;

    public function findById(string $id): ?Permission;

    public function paginate(int $perPage, int $page = 1): array;

    public function deleteById(string $id): void;
}
