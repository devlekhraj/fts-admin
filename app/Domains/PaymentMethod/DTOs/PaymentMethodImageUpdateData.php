<?php

declare(strict_types=1);

namespace App\Domains\PaymentMethod\DTOs;

final class PaymentMethodImageUpdateData
{
    public function __construct(
        public readonly string $altText,
        public readonly bool $isDefault,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            altText: trim((string) ($data['alt_text'] ?? '')),
            isDefault: (bool) ($data['is_default'] ?? false),
        );
    }
}

