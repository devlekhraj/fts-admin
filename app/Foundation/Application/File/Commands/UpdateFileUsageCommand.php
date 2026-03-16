<?php

declare(strict_types=1);

namespace App\Foundation\Application\File\Commands;

final class UpdateFileUsageCommand
{
    public function __construct(
        public readonly int $fileUsageId,
        public readonly string $altText,
        public readonly ?string $title = null,
        public readonly ?string $link = null,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly int $seqNo = 0,
        public readonly bool $isActive = false,
    ) {}
}
