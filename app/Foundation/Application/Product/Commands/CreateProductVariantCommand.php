<?php

declare(strict_types=1);

namespace App\Foundation\Application\Product\Commands;

final class CreateProductVariantCommand
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public readonly int $productId,
        public readonly float $price,
        public readonly int $quantity,
        public readonly array $attributes = [],
    ) {}
}
