<?php

declare(strict_types=1);

namespace App\Domains\Admin\DTOs;

final class UpdateAdminRoleData
{
    public function __construct(
        public readonly int $roleId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            roleId: (int) ($data['role_id'] ?? 0),
        );
    }
}

