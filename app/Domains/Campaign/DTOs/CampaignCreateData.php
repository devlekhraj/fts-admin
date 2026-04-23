<?php

declare(strict_types=1);

namespace App\Domains\Campaign\DTOs;

final class CampaignCreateData
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $slug,
        public readonly ?string $startDate,
        public readonly ?string $endDate,
        public readonly ?bool $isPublished,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: (string) ($data['title'] ?? ''),
            slug: isset($data['slug']) ? (string) $data['slug'] : null,
            startDate: isset($data['start_date']) ? (string) $data['start_date'] : null,
            endDate: isset($data['end_date']) ? (string) $data['end_date'] : null,
            isPublished: array_key_exists('is_published', $data) ? (bool) $data['is_published'] : null,
        );
    }
}

