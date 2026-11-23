<?php

namespace App\Domains\Exchange\Support;

/** Same IMEI and serial rules as the customer website, so a request entered by staff is held to the same standard. */
class ImeiValidator
{
    /** Upper-case, no spaces or dashes: the form saved and compared for duplicates. */
    public static function normalize(string $value): string
    {
        return strtoupper(preg_replace('/[\s\-]+/', '', trim($value)) ?? '');
    }

    /** A 15-digit IMEI whose last digit satisfies the Luhn check. */
    public static function isValidImei(string $imei): bool
    {
        if (!preg_match('/^\d{15}$/', $imei)) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 15; $i++) {
            $digit = (int) $imei[14 - $i];
            if ($i % 2 === 1) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }

        return $sum % 10 === 0;
    }

    /**
     * A phone must have a real IMEI: exactly 15 digits that pass the check. Laptops and other
     * devices have a serial number instead, which is only checked for length and characters.
     */
    public static function checkFor(string $normalized, bool $isLaptop): ?string
    {
        if ($isLaptop) {
            return self::check($normalized);
        }

        if ($normalized === '') {
            return 'Enter the IMEI of your phone.';
        }

        if (!preg_match('/^\d{15}$/', $normalized)) {
            return 'The IMEI must be exactly 15 digits. Dial *#06# on the phone to see it.';
        }

        return self::isValidImei($normalized)
            ? null
            : 'This IMEI is not valid. Dial *#06# on the phone to see the correct 15-digit number.';
    }

    /**
     * Returns an error message, or null when the value is acceptable.
     * Digits only means it is meant as an IMEI and must be valid; anything else is treated as a
     * serial number (laptops, tablets) and only checked for length and characters.
     */
    public static function check(string $normalized): ?string
    {
        if ($normalized === '') {
            return 'Enter the IMEI or serial number of your device.';
        }

        if (ctype_digit($normalized)) {
            return self::isValidImei($normalized)
                ? null
                : 'This IMEI is not valid. Dial *#06# on the phone to see the correct 15-digit number.';
        }

        if (!preg_match('/^[A-Z0-9]{5,40}$/', $normalized)) {
            return 'The serial number may contain only letters and numbers (5 to 40 characters).';
        }

        return null;
    }
}
