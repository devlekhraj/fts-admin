<?php

declare(strict_types=1);

namespace App\Domains\BlogCategory\DTOs;

final class BlogCategoryImageUpdateData
{
    public function __construct(
        public readonly string $altText,
        public readonly bool $isDefault,
        public readonly bool $isActive,
    ) {}

    public static function fromArray(array $data): self
    {
        $isDefault = array_key_exists('is_default', $data) ? (bool) ($data['is_default'] ?? false) : false;
        $isActive = array_key_exists('is_active', $data) ? (bool) ($data['is_active'] ?? false) : false;

        return new self(
            altText: (string) ($data['alt_text'] ?? ''),
            isDefault: $isDefault,
            isActive: $isActive,
        );
    }
}

