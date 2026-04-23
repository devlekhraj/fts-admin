<?php

declare(strict_types=1);

namespace App\Domains\File\DTOs;

use Illuminate\Http\UploadedFile;

final class FileAssignData
{
    public function __construct(
        public readonly string $source,
        public readonly string $usageType,
        public readonly int $usageId,
        public readonly ?int $imageId,
        public readonly ?UploadedFile $file,
        public readonly ?string $fileName,
        public readonly string $altText,
        public readonly ?string $caption,
        public readonly ?string $description,
        public readonly bool $isDefault,
    ) {}

    public static function fromArray(array $validated, ?UploadedFile $file): self
    {
        return new self(
            source: (string) ($validated['source'] ?? ''),
            usageType: trim((string) ($validated['usage_type'] ?? '')),
            usageId: (int) ($validated['usage_id'] ?? 0),
            imageId: isset($validated['image_id']) ? (int) $validated['image_id'] : null,
            file: $file,
            fileName: isset($validated['file_name']) ? (string) $validated['file_name'] : null,
            altText: isset($validated['alt_text']) ? trim((string) $validated['alt_text']) : '',
            caption: isset($validated['caption']) ? (string) $validated['caption'] : null,
            description: isset($validated['description']) ? (string) $validated['description'] : null,
            isDefault: (bool) ($validated['is_default'] ?? false),
        );
    }
}

