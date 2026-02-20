<?php

declare(strict_types=1);

namespace App\Foundation\Application\AdminIdentity\Commands;

final class UpdateAdminBasicCommand
{
    public function __construct(
        public readonly string $id,
        public readonly string $username,
        public readonly string $name,
        public readonly int $roleId,
    ) {}
}
