<?php

declare(strict_types=1);

namespace App\Domains\ProductCategory\DTOs;

final class CategoryUpdateData
{
    public function __construct(
        public readonly array $attributes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(attributes: $data);
    }
}
