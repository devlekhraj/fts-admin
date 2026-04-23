<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\DTOs;

final class EmiRequestListData
{
    public function __construct(
        public readonly int $perPage,
        public readonly string $search,
        public readonly mixed $emiType,
        public readonly mixed $status,
    ) {}

    public static function fromArray(array $data): self
    {
        $search = trim((string) ($data['search'] ?? ''));

        return new self(
            perPage: (int) ($data['per_page'] ?? 10),
            search: $search,
            emiType: $data['emi_type'] ?? null,
            status: $data['status'] ?? null,
        );
    }
}

