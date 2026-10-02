<?php

declare(strict_types=1);

namespace App\Domains\Order\DTOs;

final class OrderListData
{
    public function __construct(
        public readonly string $search,
        public readonly int $perPage,
        public readonly ?bool $preOrder = null,
        public readonly string $paymentStatus = '',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            search: trim((string) ($data['search'] ?? '')),
            perPage: (int) ($data['per_page'] ?? 15),
            preOrder: array_key_exists('pre_order', $data) && $data['pre_order'] !== ''
                ? filter_var($data['pre_order'], FILTER_VALIDATE_BOOLEAN)
                : null,
            paymentStatus: trim((string) ($data['payment_status'] ?? '')),
        );
    }
}

