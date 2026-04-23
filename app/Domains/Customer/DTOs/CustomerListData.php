<?php

declare(strict_types=1);

namespace App\Domains\Customer\DTOs;

final class CustomerListData
{
    public function __construct(
        public readonly ?string $search,
        public readonly int $perPageParam,
    ) {
    }

    public static function fromArray(array $query): self
    {
        $search = isset($query['search']) ? (string) $query['search'] : null;
        $perPageParam = isset($query['per_page']) ? (int) $query['per_page'] : 15;

        return new self(search: $search, perPageParam: $perPageParam);
    }
}

