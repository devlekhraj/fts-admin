<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Pdf\Contracts;

use App\Foundation\Application\Emi\DTO\PdfResult;

interface BankPdfGenerator
{
    public function supports(string $bankCode): bool;

    public function generate(object $bankData, mixed $requestModel): PdfResult;
}

