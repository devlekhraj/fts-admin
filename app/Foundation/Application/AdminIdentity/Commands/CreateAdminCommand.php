<?php

declare(strict_types=1);

namespace App\Foundation\Application\AdminIdentity\Commands;

final class CreateAdminCommand
{
    public function __construct(
        public readonly string $username,
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly int $roleId,
    ) {}
}
