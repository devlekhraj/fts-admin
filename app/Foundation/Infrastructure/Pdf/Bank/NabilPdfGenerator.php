<?php

declare(strict_types=1);

namespace App\Foundation\Infrastructure\Pdf\Bank;

use App\Foundation\Application\Emi\DTO\PdfResult;
use App\Foundation\Infrastructure\Pdf\Contracts\BankPdfGenerator;
use App\Foundation\Infrastructure\Pdf\Renderer\PdfRenderer;
use Barryvdh\DomPDF\Facade\Pdf;

final class NabilPdfGenerator implements BankPdfGenerator
{
    public function __construct(
        private readonly PdfRenderer $renderer,
    ) {}

    public function supports(string $bankCode): bool
    {
        return strtoupper($bankCode) === 'NABIL';
    }

    public function generate(object $bankData, mixed $requestModel): PdfResult
    {
        $data = [
            'bankData' => $bankData,
            'request' => $requestModel,
            'card_holder_name' => $bankData->cardHolderName ?? null,
            'card_number' => $bankData->cardNumber ?? null,
            'expiry_date' => $bankData->expiryDate ?? null,
            'telephone_no' => $bankData->telephoneNo ?? null,
            'mobile_no' => $bankData->mobileNo ?? null,
            'merchant_name' => $bankData->merchantName ?? null,
            'merchant_phone' => $bankData->merchantPhone ?? null,
            'name_address_merchant' => $bankData->merchantAddress ?? null,
            'product_name' => $bankData->productName ?? null,
            'manufacturer' => $bankData->manufacturer ?? null,
            'model_no' => $bankData->modelNo ?? null,
            'serial_no' => $bankData->serialNo ?? null,
            'installment_amount' => $bankData->installmentAmount ?? null,
            'installment_amount_words' => $bankData->installmentAmountWords ?? null,
            'emi_tenures' => $bankData->emiTenures ?? [],
            'selected_tenure' => $bankData->selectedTenure ?? null,
            'applicant_name' => $bankData->applicantName ?? null,
            'email' => $bankData->email ?? null,
            'signature_text' => $bankData->cardHolderName ?? null,
            'date' => $bankData->applicationDate ?? null,
        ];

        $pdf = Pdf::loadView('emi.template-nabil', $data)
            ->setPaper('a4', 'portrait');

        $bytes = $pdf->output();
        $filename = 'emi-application-nabil-' . (string) ($requestModel->id ?? 'unknown') . '.pdf';

        return new PdfResult($filename, $bytes);
    }
}
