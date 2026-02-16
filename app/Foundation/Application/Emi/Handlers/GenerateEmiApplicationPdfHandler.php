<?php

declare(strict_types=1);

namespace App\Foundation\Application\Emi\Handlers;

use App\Foundation\Application\Emi\Commands\GenerateEmiApplicationPdfCommand;
use App\Foundation\Application\Emi\DTO\PdfResult;
use App\Foundation\Infrastructure\Emi\BankSchemaRegistry;
use App\Foundation\Infrastructure\Persistence\Eloquent\Query\EmiRequestReadModel;
use App\Foundation\Infrastructure\Pdf\Registry\BankPdfGeneratorRegistry;

final class GenerateEmiApplicationPdfHandler
{
    public function __construct(
        private readonly EmiRequestReadModel $emiRequestReadModel,
        private readonly BankSchemaRegistry $schemaRegistry,
        private readonly BankPdfGeneratorRegistry $pdfRegistry,
    ) {}

    public function handle(GenerateEmiApplicationPdfCommand $command): PdfResult
    {
        $emiRequest = $this->emiRequestReadModel->getForApplication($command->emiRequestId);
        $requestBankCode = (string) (request()->input('bank_code') ?? request()->input('bank') ?? '');
        $bankCode = strtoupper((string) ($requestBankCode !== '' ? $requestBankCode : ($emiRequest->bank_code ?? 'GLOBAL')));
        $selectedTenure = (int) preg_replace('/\D+/', '', (string) ($emiRequest->emi_mode ?? '0'));

        $form = request()->all();

        if ($bankCode === 'SBL') {
            $payload = [
                'cardholder_name' => (string) ($form['cardholder_name'] ?? $emiRequest->card_holder_name ?? $emiRequest->name ?? 'N/A'),
                'address' => (string) ($form['address'] ?? ''),
                'mobile' => (string) ($form['mobile'] ?? $emiRequest->contact_number ?? 'N/A'),
                'email' => (string) ($form['email'] ?? $emiRequest->email ?? 'N/A'),
                'card_number' => preg_replace('/\D+/', '', (string) ($form['card_number'] ?? $emiRequest->card_number ?? '')),
                'expiry_date' => str_replace('/', '', (string) ($form['expiry_date'] ?? $emiRequest->card_expiry_date ?? '')),
                'merchant_name_address' => (string) ($form['merchant_name_address'] ?? 'Fatafat Sewa Pvt. Ltd'),
                'item_name' => (string) ($form['item_name'] ?? $emiRequest->product?->name ?? 'N/A'),
                'manufactured_by' => (string) ($form['manufactured_by'] ?? $emiRequest->product?->brand?->name ?? 'N/A'),
                'model_name' => (string) ($form['model_name'] ?? 'N/A'),
                'serial_no' => (string) ($form['serial_no'] ?? 'N/A'),
                'emi_amount' => (float) ($form['emi_amount'] ?? $form['installment_amount'] ?? $emiRequest->down_payment ?? 0),
                'amount_in_words' => (string) ($form['amount_in_words'] ?? ''),
                'emi_tenure' => (string) ($form['emi_tenure'] ?? $form['tenure'] ?? $selectedTenure),
                'merchant_name' => (string) ($form['merchant_name'] ?? ''),
                'requested_by' => (string) ($form['requested_by'] ?? ''),
                'requested_phone' => (string) ($form['requested_phone'] ?? ''),
                'signature_file' => (string) ($form['signature_file'] ?? ''),
                'stamp_file' => (string) ($form['stamp_file'] ?? ''),
                'application_date' => (string) optional($emiRequest->created_at)->format('F j, Y'),
            ];
        } else {
            $payload = [
                'productName' => (string) ($form['item_name'] ?? $emiRequest->product?->name ?? 'N/A'),
                'manufacturer' => (string) ($form['manufactured_by'] ?? $emiRequest->product?->brand?->name ?? 'N/A'),
                'modelNo' => (string) ($form['model_name'] ?? 'N/A'),
                'serialNo' => (string) ($form['serial_no'] ?? 'N/A'),
                'installmentAmount' => (float) ($form['installment_amount'] ?? $emiRequest->down_payment ?? 0),
                'installmentAmountWords' => (string) ($form['amount_in_words'] ?? ''),
                'selectedTenure' => (int) preg_replace('/\D+/', '', (string) ($form['tenure'] ?? $selectedTenure)),
                'emiTenures' => (array) ($emiRequest->bank?->tenures ?? []),
                'merchantName' => (string) ($form['merchant_name_address'] ?? 'Fatafat Sewa Pvt. Ltd'),
                'merchantPhone' => '9813001000',
                'merchantAddress' => (string) ($form['merchant_name_address'] ?? 'Sitapaila, Kathmandu'),
                'applicantName' => (string) ($form['cardholder_name'] ?? $emiRequest->name ?? 'N/A'),
                'cardHolderName' => (string) ($form['cardholder_name'] ?? $emiRequest->card_holder_name ?? 'N/A'),
                'cardNumber' => preg_replace('/\D+/', '', (string) ($form['card_number'] ?? $emiRequest->card_number ?? '')),
                'expiryDate' => str_replace('/', '', (string) ($form['expiry_date'] ?? $emiRequest->card_expiry_date ?? '')),
                'telephoneNo' => (string) ($form['telephone'] ?? 'N/A'),
                'mobileNo' => (string) ($form['mobile'] ?? $emiRequest->contact_number ?? 'N/A'),
                'email' => (string) ($emiRequest->email ?? 'N/A'),
                'applicationDate' => (string) optional($emiRequest->created_at)->format('F j, Y'),
            ];
        }

        $bankData = $this->schemaRegistry->get($bankCode)->toData($payload);


        $pdf = $this->pdfRegistry->for($bankCode)->generate($bankData, $emiRequest);

        $bankId = $emiRequest->bank?->id ?? $emiRequest->bank;

        return new PdfResult(
            $pdf->filename,
            $pdf->bytes,
            $bankData,
            (int) $emiRequest->id,
            $bankId !== null ? (int) $bankId : null,
        );
    }
}
