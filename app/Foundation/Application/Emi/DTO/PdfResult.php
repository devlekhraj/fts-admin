<?php

declare(strict_types=1);

namespace App\Foundation\Application\Emi\DTO;

final class PdfResult
{
    public function __construct(
        public readonly string $filename,
        public readonly string $bytes,
        public readonly ?object $applicationData = null,
        public readonly ?int $emiRequestId = null,
        public readonly ?int $emiBankId = null,
    ) {}
}
