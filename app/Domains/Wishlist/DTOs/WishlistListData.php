<?php

declare(strict_types=1);

namespace App\Domains\Wishlist\DTOs;

final class WishlistListData
{
    public function __construct(
        public readonly ?string $search,
        public readonly int $perPageParam,
    ) {
    }

    public static function fromArray(array $query): self
    {
        $search = isset($query['search']) ? (string) $query['search'] : null;
        $perPageParam = isset($query['per_page']) ? (int) $query['per_page'] : 12;

        return new self(search: $search, perPageParam: $perPageParam);
    }
}

