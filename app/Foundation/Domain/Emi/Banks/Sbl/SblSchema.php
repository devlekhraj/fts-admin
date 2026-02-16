<?php

declare(strict_types=1);

namespace App\Foundation\Domain\Emi\Banks\Sbl;

use App\Foundation\Domain\Emi\Banks\Contracts\BankApplicationSchema;
use App\Foundation\Shared\Domain\Exceptions\DomainException;

final class SblSchema implements BankApplicationSchema
{
    public function bankCode(): string
    {
        return 'SBL';
    }

    public function validate(array $payload): void
    {
        foreach ([
            'cardholder_name',
            'address',
            'mobile',
            'email',
            'card_number',
            'expiry_date',
            'merchant_name_address',
            'item_name',
            'manufactured_by',
            'model_name',
            'serial_no',
            'emi_amount',
            'amount_in_words',
            'emi_tenure',
            'merchant_name',
            'requested_by',
            'requested_phone',
            'application_date',
        ] as $field) {
            if (!isset($payload[$field]) || $payload[$field] === '') {
                throw new DomainException("Field '{$field}' is required for SBL.");
            }
        }

        if (!is_numeric($payload['emi_amount'])) {
            throw new DomainException("Field 'emi_amount' must be numeric.");
        }
    }

    public function toData(array $payload): SblApplicationData
    {
        $this->validate($payload);

        return new SblApplicationData(
            cardholder_name: (string) $payload['cardholder_name'],
            address: (string) $payload['address'],
            mobile: (string) $payload['mobile'],
            email: (string) $payload['email'],
            card_number: (string) $payload['card_number'],
            expiry_date: (string) $payload['expiry_date'],
            merchant_name_address: (string) $payload['merchant_name_address'],
            item_name: (string) $payload['item_name'],
            manufactured_by: (string) $payload['manufactured_by'],
            model_name: (string) $payload['model_name'],
            serial_no: (string) $payload['serial_no'],
            emi_amount: (float) $payload['emi_amount'],
            amount_in_words: (string) $payload['amount_in_words'],
            emi_tenure: (string) $payload['emi_tenure'],
            merchant_name: (string) $payload['merchant_name'],
            requested_by: (string) $payload['requested_by'],
            requested_phone: (string) $payload['requested_phone'],
            signature_file: (string) ($payload['signature_file'] ?? ''),
            stamp_file: (string) ($payload['stamp_file'] ?? ''),
            application_date: (string) $payload['application_date'],
        );
    }
}
