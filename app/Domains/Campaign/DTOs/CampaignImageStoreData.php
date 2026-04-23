<?php

declare(strict_types=1);

namespace App\Domains\Campaign\DTOs;

use Illuminate\Http\UploadedFile;

final class CampaignImageStoreData
{
    public function __construct(
        public readonly UploadedFile $image,
        public readonly ?string $altText,
        public readonly ?string $link,
        public readonly ?string $startDate,
        public readonly ?string $endDate,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            image: $data['image'],
            altText: isset($data['alt_text']) ? (string) $data['alt_text'] : null,
            link: isset($data['link']) ? (string) $data['link'] : null,
            startDate: isset($data['start_date']) ? (string) $data['start_date'] : null,
            endDate: isset($data['end_date']) ? (string) $data['end_date'] : null,
        );
    }
}

