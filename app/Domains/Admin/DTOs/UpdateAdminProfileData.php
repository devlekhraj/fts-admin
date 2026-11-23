<?php

declare(strict_types=1);

namespace App\Domains\Admin\DTOs;

final class UpdateAdminProfileData
{
    public function __construct(
        public readonly string $name,
        public readonly string $username,
        public readonly ?string $avatar = null,
        public readonly ?int $roleId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $avatar = $data['avatar'] ?? null;

        return new self(
            name: (string) ($data['name'] ?? ''),
            username: (string) ($data['username'] ?? ''),
            avatar: is_string($avatar) ? trim($avatar) : null,
            roleId: isset($data['role_id']) && $data['role_id'] !== '' ? (int) $data['role_id'] : null,
        );
    }
}

