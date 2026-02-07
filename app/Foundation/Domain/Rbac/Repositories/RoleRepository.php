<?php

declare(strict_types=1);

namespace App\Foundation\Domain\Rbac\Repositories;

use App\Foundation\Domain\Rbac\Entities\Role;

interface RoleRepository
{
    public function save(Role $entity): void;

    public function findById(string $id): ?Role;

    public function paginate(int $perPage, int $page = 1): array;

    public function deleteById(string $id): void;
}
