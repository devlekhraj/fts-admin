<?php

declare(strict_types=1);

namespace App\Foundation\Application\AdminIdentity\Commands;

final class UpdateAdminPasswordCommand
{
    public function __construct(
        public readonly string $id,
        public readonly string $password,
    ) {}
}
