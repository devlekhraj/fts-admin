<?php

declare(strict_types=1);

namespace App\Domains\Campaign\DTOs;

final class CampaignAssignProductsData
{
    /**
     * @param array<int> $productIds
     */
    public function __construct(
        public readonly array $productIds,
        public readonly ?int $discountType,
        public readonly ?float $discountValue,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $productIds = $data['product_ids'] ?? [];
        if (! is_array($productIds)) {
            $productIds = [];
        }

        return new self(
            productIds: array_values(array_map('intval', $productIds)),
            discountType: array_key_exists('discount_type', $data) && $data['discount_type'] !== null ? (int) $data['discount_type'] : null,
            discountValue: array_key_exists('discount_value', $data) && $data['discount_value'] !== null ? (float) $data['discount_value'] : null,
        );
    }
}

