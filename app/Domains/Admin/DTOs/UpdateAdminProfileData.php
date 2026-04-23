<?php

declare(strict_types=1);

namespace App\Domains\Admin\DTOs;

final class UpdateAdminProfileData
{
    public function __construct(
        public readonly string $name,
        public readonly string $username,
        public readonly ?string $avatar = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $avatar = $data['avatar'] ?? null;

        return new self(
            name: (string) ($data['name'] ?? ''),
            username: (string) ($data['username'] ?? ''),
            avatar: is_string($avatar) ? trim($avatar) : null,
        );
    }
}

