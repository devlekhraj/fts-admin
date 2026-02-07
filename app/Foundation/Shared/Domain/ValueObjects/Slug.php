<?php

declare(strict_types=1);

namespace App\Foundation\Shared\Domain\ValueObjects;

final class Slug
{
    public function __construct(private string $value)
    {
        // TODO: Validate slug format.
    }

    public function value(): string
    {
        return $this->value;
    }
}
