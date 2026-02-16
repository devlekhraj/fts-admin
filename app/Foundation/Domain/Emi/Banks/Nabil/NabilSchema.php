<?php

declare(strict_types=1);

namespace App\Foundation\Domain\Emi\Banks\Nabil;

use App\Foundation\Domain\Emi\Banks\Contracts\BankApplicationSchema;
use App\Foundation\Shared\Domain\Exceptions\DomainException;

final class NabilSchema implements BankApplicationSchema
{
    public function bankCode(): string
    {
        return 'NABIL';
    }

    public function validate(array $payload): void
    {
        $requiredFields = [
            'productName',
            'manufacturer',
            'modelNo',
            'serialNo',
            'installmentAmount',
            'selectedTenure',
            'emiTenures',
            'merchantName',
            'merchantPhone',
            'merchantAddress',
            'applicantName',
            'cardHolderName',
            'cardNumber',
            'expiryDate',
            'telephoneNo',
            'mobileNo',
            'email',
            'applicationDate',
        ];

        foreach ($requiredFields as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '') {
                throw new DomainException("Field '{$field}' is required for NABIL.");
            }
        }

        if (!is_array($payload['emiTenures'])) {
            throw new DomainException("Field 'emiTenures' must be an array.");
        }

        if (!is_numeric($payload['installmentAmount'])) {
            throw new DomainException("Field 'installmentAmount' must be numeric.");
        }

        if (!is_numeric($payload['selectedTenure'])) {
            throw new DomainException("Field 'selectedTenure' must be numeric.");
        }
    }

    public function toData(array $payload): NabilApplicationData
    {
        $this->validate($payload);

        return new NabilApplicationData(
            productName: (string) $payload['productName'],
            manufacturer: (string) $payload['manufacturer'],
            modelNo: (string) $payload['modelNo'],
            serialNo: (string) $payload['serialNo'],
            installmentAmount: (float) $payload['installmentAmount'],
            installmentAmountWords: (string) ($payload['installmentAmountWords'] ?? ''),
            selectedTenure: (int) $payload['selectedTenure'],
            emiTenures: $payload['emiTenures'],
            merchantName: (string) $payload['merchantName'],
            merchantPhone: (string) $payload['merchantPhone'],
            merchantAddress: (string) $payload['merchantAddress'],
            applicantName: (string) $payload['applicantName'],
            cardHolderName: (string) $payload['cardHolderName'],
            cardNumber: (string) $payload['cardNumber'],
            expiryDate: (string) $payload['expiryDate'],
            telephoneNo: (string) $payload['telephoneNo'],
            mobileNo: (string) $payload['mobileNo'],
            email: (string) $payload['email'],
            applicationDate: (string) $payload['applicationDate'],
        );
    }
}
