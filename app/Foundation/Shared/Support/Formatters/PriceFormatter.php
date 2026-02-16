<?php

declare(strict_types=1);

namespace App\Foundation\Shared\Support\Formatters;

final class PriceFormatter
{
    public static function format(int|float $amount, string $currency = 'Rs'): string
    {
        return $currency . ' ' . (fmod($amount, 1) == 0.0 ? number_format($amount, 0) : number_format($amount, 2));
    }
}
