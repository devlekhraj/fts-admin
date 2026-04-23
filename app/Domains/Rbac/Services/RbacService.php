<?php

declare(strict_types=1);

namespace App\Domains\Rbac\Services;

use App\Domains\Rbac\Actions\ListRolesAction;
use Illuminate\Database\Eloquent\Collection;

final class RbacService
{
    public function __construct(
        private readonly ListRolesAction $listRolesAction,
    ) {}

    public function roles(): Collection
    {
        return $this->listRolesAction->execute();
    }
}

