<?php

declare(strict_types=1);

namespace App\Domains\Faq\DTOs;

final class FaqListData
{
    public function __construct(
        public readonly string $type,
        public readonly ?string $typeId,
        public readonly ?string $search,
        public readonly int $perPageParam,
    ) {
    }

    public static function fromArray(array $query): self
    {
        $type = trim((string) ($query['type'] ?? ''));
        $typeId = isset($query['type_id']) ? trim((string) $query['type_id']) : null;
        $typeId = $typeId !== '' ? $typeId : null;
        $search = isset($query['search']) ? (string) $query['search'] : null;
        $perPageParam = isset($query['per_page']) ? (int) $query['per_page'] : 15;

        return new self(
            type: $type,
            typeId: $typeId,
            search: $search,
            perPageParam: $perPageParam,
        );
    }
}

