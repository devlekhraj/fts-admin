<?php

declare(strict_types=1);

namespace App\Domains\Order\DTOs;

final class OrderListData
{
    public function __construct(
        public readonly string $search,
        public readonly int $perPage,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            search: trim((string) ($data['search'] ?? '')),
            perPage: (int) ($data['per_page'] ?? 15),
        );
    }
}

