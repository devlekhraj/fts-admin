<?php

declare(strict_types=1);

namespace App\Domains\Campaign\DTOs;

final class CampaignDiscountUpdateData
{
    public function __construct(
        public readonly ?int $discountType,
        public readonly ?float $discountValue,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            discountType: array_key_exists('discount_type', $data) && $data['discount_type'] !== null ? (int) $data['discount_type'] : null,
            discountValue: array_key_exists('discount_value', $data) && $data['discount_value'] !== null ? (float) $data['discount_value'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'discount_type' => $this->discountType,
            'discount_value' => $this->discountValue,
        ], static fn ($v) => ! is_null($v));
    }
}

