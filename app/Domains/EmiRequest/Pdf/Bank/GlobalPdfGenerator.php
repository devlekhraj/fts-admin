<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Pdf\Bank;

use App\DTO\PdfResultDto;
use App\Domains\EmiRequest\Pdf\Contracts\BankPdfGenerator;
use App\Domains\EmiRequest\Pdf\Renderer\PdfRenderer;

final class GlobalPdfGenerator implements BankPdfGenerator
{
    public function __construct(
        private readonly PdfRenderer $renderer,
    ) {}

    public function supports(string $bankCode): bool
    {
        return strtoupper($bankCode) === 'GLOBAL';
    }

    public function generate(object $bankData, mixed $requestModel): PdfResultDto
    {
        $html = view('pdf.emi.global', [
            'bankData' => $bankData,
            'request' => $requestModel,
        ])->render();

        $bytes = $this->renderer->renderHtml($html);
        $filename = 'emi-application-global-' . (string) ($requestModel->id ?? 'unknown') . '.pdf';

        return new PdfResultDto($filename, $bytes);
    }
}

