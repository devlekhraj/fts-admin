<?php

declare(strict_types=1);

namespace App\Domains\Blog\DTOs;

final class BlogUpdateData
{
    public function __construct(
        public readonly array $attributes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(attributes: $data);
    }
}

