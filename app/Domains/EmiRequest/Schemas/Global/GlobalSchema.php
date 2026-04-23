<?php

declare(strict_types=1);

namespace App\Domains\EmiRequest\Schemas\Global;

use App\Domains\EmiRequest\Schemas\Contracts\BankApplicationSchema;
use App\Support\Exceptions\DomainException;

final class GlobalSchema implements BankApplicationSchema
{
    public function bankCode(): string
    {
        return 'GLOBAL';
    }

    public function validate(array $payload): void
    {
        foreach (['productName', 'installmentAmount', 'selectedTenure', 'applicantName', 'applicationDate'] as $field) {
            if (! isset($payload[$field]) || $payload[$field] === '') {
                throw new DomainException("Field '{$field}' is required for GLOBAL.");
            }
        }

        if (! is_numeric($payload['installmentAmount'])) {
            throw new DomainException("Field 'installmentAmount' must be numeric.");
        }

        if (! is_numeric($payload['selectedTenure'])) {
            throw new DomainException("Field 'selectedTenure' must be numeric.");
        }
    }

    public function toData(array $payload): GlobalApplicationData
    {
        $this->validate($payload);

        return new GlobalApplicationData(
            productName: (string) $payload['productName'],
            installmentAmount: (float) $payload['installmentAmount'],
            selectedTenure: (int) $payload['selectedTenure'],
            applicantName: (string) $payload['applicantName'],
            applicationDate: (string) $payload['applicationDate'],
        );
    }
}

