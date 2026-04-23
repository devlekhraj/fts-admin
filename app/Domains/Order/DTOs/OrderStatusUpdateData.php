<?php

declare(strict_types=1);

namespace App\Domains\Order\DTOs;

final class OrderStatusUpdateData
{
    public function __construct(
        public readonly int $status,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: (int) $data['status'],
        );
    }
}

