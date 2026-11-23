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
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $search = trim((string) ($data['search'] ?? ''));

        return new self(
            perPage: (int) ($data['per_page'] ?? 10),
            search: $search,
            emiType: $data['emi_type'] ?? null,
            status: $data['status'] ?? null,
            dateFrom: self::date($data['date_from'] ?? null),
            dateTo: self::date($data['date_to'] ?? null),
        );
    }

    private static function date(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $text) === 1 ? $text : null;
    }
}

