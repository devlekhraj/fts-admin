<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Pdf\Bank;

use App\Foundation\Application\Emi\DTO\PdfResult;
use App\Foundation\Infrastructure\Pdf\Contracts\BankPdfGenerator;
use App\Foundation\Infrastructure\Pdf\Renderer\PdfRenderer;
use Barryvdh\DomPDF\Facade\Pdf;

final class SiddarthaPdfGenerator implements BankPdfGenerator
{
    public function __construct(
        private readonly PdfRenderer $renderer,
    ) {}

    public function supports(string $bankCode): bool
    {
        return strtoupper($bankCode) === 'SBL';
    }

    public function generate(object $bankData, mixed $requestModel): PdfResult
    {
        $data = [
            'bankData' => $bankData,
            'request' => $requestModel,
            'card_holder_name' => $bankData->cardholder_name ?? null,
            'address' => $bankData->address ?? null,
            'mobile_no' => $bankData->mobile ?? null,
            'email' => $bankData->email ?? null,
            'card_number' => $bankData->card_number ?? null,
            'expiry_date' => $bankData->expiry_date ?? null,
            'telephone_no' => $bankData->requested_phone ?? null,
            'name_address_merchant' => $bankData->merchant_name_address ?? null,
            'product_name' => $bankData->item_name ?? null,
            'manufacturer' => $bankData->manufactured_by ?? null,
            'model_no' => $bankData->model_name ?? null,
            'serial_no' => $bankData->serial_no ?? null,
            'emi_amount' => $bankData->emi_amount ?? null,
            'amount_in_words' => $bankData->amount_in_words ?? null,
            'emi_tenure' => $bankData->emi_tenure ?? null,
            'merchant_name' => $bankData->merchant_name ?? null,
            'requested_by' => $bankData->requested_by ?? null,
            'merchant_phone' => $bankData->requested_phone ?? null,
            'signature_text' => $bankData->cardholder_name ?? null,
            'date' => $bankData->application_date ?? null,
            'emi_tenures' => (array) ($requestModel->bank?->tenures ?? []),
            'selected_tenure' => $bankData->emi_tenure ?? null,
        ];

        $pdf = Pdf::loadView('emi.template-sbl', $data)
            ->setPaper('a4', 'portrait');

        $bytes = $pdf->output();
        $filename = 'emi-application-sbl-' . (string) ($requestModel->id ?? 'unknown') . '.pdf';

        return new PdfResult($filename, $bytes);
    }
}
