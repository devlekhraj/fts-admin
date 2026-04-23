<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Schemas\Global;

final class GlobalApplicationData
{
    public function __construct(
        public readonly string $productName,
        public readonly float $installmentAmount,
        public readonly int $selectedTenure,
        public readonly string $applicantName,
        public readonly string $applicationDate,
    ) {}
}

