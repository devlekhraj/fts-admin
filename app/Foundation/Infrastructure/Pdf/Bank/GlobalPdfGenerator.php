<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Pdf\Bank;

use App\Foundation\Application\Emi\DTO\PdfResult;
use App\Foundation\Infrastructure\Pdf\Contracts\BankPdfGenerator;
use App\Foundation\Infrastructure\Pdf\Renderer\PdfRenderer;

final class GlobalPdfGenerator implements BankPdfGenerator
{
    public function __construct(
        private readonly PdfRenderer $renderer,
    ) {}

    public function supports(string $bankCode): bool
    {
        return strtoupper($bankCode) === 'GLOBAL';
    }

    public function generate(object $bankData, mixed $requestModel): PdfResult
    {
        $html = view('pdf.emi.global', [
            'bankData' => $bankData,
            'request' => $requestModel,
        ])->render();

        $bytes = $this->renderer->renderHtml($html);
        $filename = 'emi-application-global-' . (string) ($requestModel->id ?? 'unknown') . '.pdf';

        return new PdfResult($filename, $bytes);
    }
}

