<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Pdf\Contracts;

use App\Domains\EmiRequest\DTOs\PdfResultDto;

interface BankPdfGenerator
{
    public function supports(string $bankCode): bool;

    public function generate(object $bankData, mixed $requestModel): PdfResultDto;
}
