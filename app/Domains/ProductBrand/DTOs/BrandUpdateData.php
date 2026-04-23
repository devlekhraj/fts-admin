<?php

declare(strict_types=1);

namespace App\Domains\ProductBrand\DTOs;

final class BrandUpdateData
{
    public function __construct(
        public readonly array $attributes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(attributes: $data);
    }
}
