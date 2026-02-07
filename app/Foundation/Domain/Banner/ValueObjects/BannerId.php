<?php

declare(strict_types=1);

namespace App\Foundation\Domain\Banner\ValueObjects;

final class BannerId
{
    public function __construct(private string $value)
    {
        // TODO: Validate value.
    }

    public function value(): string
    {
        return $this->value;
    }
}
