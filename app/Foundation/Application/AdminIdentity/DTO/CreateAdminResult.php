<?php

declare(strict_types=1);

namespace App\Foundation\Application\AdminIdentity\DTO;

final class CreateAdminResult
{
    public function __construct(
        public readonly bool $created,
        public readonly string $message,
        public readonly ?string $field = null,
    ) {}
}
