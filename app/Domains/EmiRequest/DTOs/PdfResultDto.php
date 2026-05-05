<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\DTOs;

final class PdfResultDto
{
    public function __construct(
        public readonly string $filename,
        public readonly string $bytes,
        public readonly ?object $applicationData = null,
        public readonly ?int $emiRequestId = null,
        public readonly ?int $emiBankId = null,
    ) {}
}
