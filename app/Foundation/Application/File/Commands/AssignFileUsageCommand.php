<?php

declare(strict_types=1);

namespace App\Foundation\Application\File\Commands;

final class AssignFileUsageCommand
{
    public function __construct(
        public readonly int $fileId,
        public readonly string $usageType,
        public readonly int $usageId,
        public readonly string $altText,
        public readonly ?string $caption = null,
        public readonly ?string $description = null,
    ) {}
}
