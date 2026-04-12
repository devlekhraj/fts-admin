<?php

declare(strict_types=1);

namespace App\Foundation\Application\File\DTO;

use Illuminate\Http\UploadedFile;

final class ImageDto
{
    public function __construct(
        public readonly string $usageType,
        public readonly int $usageId,
        public readonly string $altText,
        public readonly array $meta = [],
        public readonly string $source = 'upload',
        public readonly ?UploadedFile $file = null,
        public readonly ?int $imageId = null,
        public readonly ?string $directory = null,
        public readonly bool $isDefault = false,
    ) {}
}
