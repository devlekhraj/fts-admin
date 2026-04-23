<?php

declare(strict_types=1);

namespace App\Domains\EmiBank\DTOs;

final class EmiBankListData
{
    public function __construct(
        public readonly int $perPage,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            perPage: (int) ($data['per_page'] ?? 10),
        );
    }
}

