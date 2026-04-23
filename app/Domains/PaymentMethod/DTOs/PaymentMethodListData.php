<?php

declare(strict_types=1);

namespace App\Domains\PaymentMethod\DTOs;

final class PaymentMethodListData
{
    public function __construct(
        public readonly ?string $search,
        public readonly int $perPageParam,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $search = isset($data['search']) ? (string) $data['search'] : null;
        $perPageParam = isset($data['per_page']) ? (int) $data['per_page'] : 15;

        return new self(search: $search, perPageParam: $perPageParam);
    }
}

