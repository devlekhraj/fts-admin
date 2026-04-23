<?php

declare(strict_types=1);

namespace App\Domains\Blog\DTOs;

final class BlogImageUpdateData
{
    public function __construct(
        public readonly string $altText,
        public readonly bool $isDefault,
    ) {}

    public static function fromArray(array $data): self
    {
        $isDefault = array_key_exists('is_default', $data) ? (bool) ($data['is_default'] ?? false) : false;

        return new self(
            altText: (string) ($data['alt_text'] ?? ''),
            isDefault: $isDefault,
        );
    }
}

