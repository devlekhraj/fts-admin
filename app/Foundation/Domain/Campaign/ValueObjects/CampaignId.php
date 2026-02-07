<?php

declare(strict_types=1);

namespace App\Foundation\Domain\Campaign\ValueObjects;

final class CampaignId
{
    public function __construct(private string $value)
    {
        // TODO: Validate value.
    }

    public function value(): string
    {
        return $this->value;
    }
}
