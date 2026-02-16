<?php

declare(strict_types=1);

namespace App\Foundation\Shared\Support\Formatters;

use Carbon\CarbonInterface;

final class DateFormatter
{
    public static function format(CarbonInterface|string $date, string $format = 'd M Y'): string
    {
        if (is_string($date)) {
            $date = new \Carbon\Carbon($date);
        }

        return $date->format($format);
    }
}
