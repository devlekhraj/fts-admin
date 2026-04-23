<?php

declare(strict_types=1);

namespace App\Domains\Banner\DTOs;

final class BannerUpdateData
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly bool $status,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            slug: (string) ($data['slug'] ?? ''),
            status: (bool) ($data['status'] ?? false),
        );
    }
}

