<?php

declare(strict_types=1);

namespace App\Domains\Order\DTOs;

final class OrderStatusUpdateData
{
    public function __construct(
        public readonly int $status,
        public readonly ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: (int) $data['status'],
            notes: array_key_exists('notes', $data) ? (is_string($data['notes']) ? trim($data['notes']) : null) : null,
        );
    }
}
