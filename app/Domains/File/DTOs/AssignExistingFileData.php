<?php

declare(strict_types=1);

namespace App\Domains\File\DTOs;

final class AssignExistingFileData
{
    public function __construct(
        public readonly int $imageId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            imageId: (int) ($data['image_id'] ?? 0),
        );
    }
}
