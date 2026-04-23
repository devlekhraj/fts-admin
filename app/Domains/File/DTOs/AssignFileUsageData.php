<?php

declare(strict_types=1);

namespace App\Domains\File\DTOs;

final class AssignFileUsageData
{
    public function __construct(
        public readonly int $fileId,
        public readonly string $usageType,
        public readonly int $usageId,
        public readonly string $altText,
        public readonly ?string $caption = null,
        public readonly ?string $description = null,
        public readonly bool $isDefault = false,
    ) {}
}
