<?php

declare(strict_types=1);

namespace App\Domains\Admin\DTOs;

final class CreateAdminData
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $username,
        public readonly int $roleId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            email: (string) ($data['email'] ?? ''),
            username: (string) ($data['username'] ?? ''),
            roleId: (int) ($data['role_id'] ?? 0),
        );
    }
}

