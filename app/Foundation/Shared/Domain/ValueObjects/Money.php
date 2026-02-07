<?php

declare(strict_types=1);

namespace App\Foundation\Shared\Domain\ValueObjects;

final class Money
{
    public function __construct(private int $amount, private string $currency)
    {
        // TODO: Validate currency and amount.
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }
}
