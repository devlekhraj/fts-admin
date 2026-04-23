<?php

declare(strict_types=1);

namespace App\Domains\Campaign\DTOs;

final class CampaignUpdateData
{
    public function __construct(public readonly array $attributes)
    {
    }

    public static function fromArray(array $data): self
    {
        unset($data['id']);

        return new self(attributes: $data);
    }
}

