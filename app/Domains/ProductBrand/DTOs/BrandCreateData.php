<?php

declare(strict_types=1);

namespace App\Domains\ProductBrand\DTOs;

final class BrandCreateData
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly bool $status,
    ) {}

    public static function fromArray(array $data): self
    {
        $status = array_key_exists('status', $data) ? (bool) ($data['status'] ?? false) : true;

        return new self(
            name: (string) ($data['name'] ?? ''),
            slug: (string) ($data['slug'] ?? ''),
            status: $status,
        );
    }
}
