<?php

declare(strict_types=1);

namespace App\Domains\Rbac\Actions;

use App\Domains\Rbac\Models\Role;
use Illuminate\Database\Eloquent\Collection;

final class ListRolesAction
{
    /**
     * @return Collection<int, Role>
     */
    public function execute(): Collection
    {
        return Role::query()->get();
    }
}

