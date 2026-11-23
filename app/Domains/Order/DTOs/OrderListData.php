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
        /** @var int[]|null order statuses to show (0 to 6), null for all */
        public readonly ?array $statuses = null,
        public readonly ?string $dateFrom = null,
        public readonly ?string $dateTo = null,
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
            statuses: self::statuses($data['status'] ?? null),
            dateFrom: self::date($data['date_from'] ?? null),
            dateTo: self::date($data['date_to'] ?? null),
        );
    }

    /** "1,2" => [1, 2]; anything outside 0 to 6 is dropped. A given but unusable value gives [] (show nothing). */
    private static function statuses(mixed $value): ?array
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return null;
        }

        $statuses = [];
        foreach (explode(',', $text) as $part) {
            $part = trim($part);
            if ($part !== '' && ctype_digit($part) && (int) $part <= 6) {
                $statuses[] = (int) $part;
            }
        }

        return array_values(array_unique($statuses));
    }

    private static function date(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $text) === 1 ? $text : null;
    }
}

