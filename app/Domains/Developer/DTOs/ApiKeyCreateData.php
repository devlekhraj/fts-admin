<?php

declare(strict_types=1);

namespace App\Domains\Developer\DTOs;

final class ApiKeyCreateData
{
    public function __construct(public readonly array $attributes)
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(attributes: $data);
    }
}

