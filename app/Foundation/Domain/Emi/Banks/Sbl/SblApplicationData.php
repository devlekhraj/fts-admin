<?php

declare(strict_types=1);

namespace App\Foundation\Domain\Emi\Banks\Sbl;

final class SblApplicationData
{
    public function __construct(
        public readonly string $cardholder_name,
        public readonly string $address,
        public readonly string $mobile,
        public readonly string $email,
        public readonly string $card_number,
        public readonly string $expiry_date,
        public readonly string $merchant_name_address,
        public readonly string $item_name,
        public readonly string $manufactured_by,
        public readonly string $model_name,
        public readonly string $serial_no,
        public readonly float $emi_amount,
        public readonly string $amount_in_words,
        public readonly string $emi_tenure,
        public readonly string $merchant_name,
        public readonly string $requested_by,
        public readonly string $requested_phone,
        public readonly string $signature_file,
        public readonly string $stamp_file,
        public readonly string $application_date,
    ) {}
}
