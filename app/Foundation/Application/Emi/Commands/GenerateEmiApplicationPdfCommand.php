<?php

declare(strict_types=1);

namespace App\Foundation\Application\Emi\Commands;

final class GenerateEmiApplicationPdfCommand
{
    public function __construct(
        public readonly string $emiRequestId,
    ) {}
}
