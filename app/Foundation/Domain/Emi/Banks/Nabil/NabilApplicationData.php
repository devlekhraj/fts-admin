<?php

declare(strict_types=1);

namespace App\Foundation\Domain\Emi\Banks\Nabil;

final class NabilApplicationData
{
    public function __construct(
        // Product Info
        public readonly string $productName,
        public readonly string $manufacturer,
        public readonly string $modelNo,
        public readonly string $serialNo,

        // Financial
        public readonly float $installmentAmount,
        public readonly string $installmentAmountWords,
        public readonly int $selectedTenure,
        public readonly array $emiTenures,

        // Merchant Info
        public readonly string $merchantName,
        public readonly string $merchantPhone,
        public readonly string $merchantAddress,

        // Applicant Info
        public readonly string $applicantName,
        public readonly string $cardHolderName,
        public readonly string $cardNumber,
        public readonly string $expiryDate,
        public readonly string $telephoneNo,
        public readonly string $mobileNo,
        public readonly string $email,

        // Dates
        public readonly string $applicationDate,
    ) {}
}
