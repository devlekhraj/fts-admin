<?php

declare(strict_types=1);

namespace App\Foundation\Shared\Support\Formatters;

final class FileDimensionFormatter
{
    public static function format(int|float|string|null $width, int|float|string|null $height): string
    {
        $w = is_numeric($width) ? (int) max(0, (float) $width) : 0;
        $h = is_numeric($height) ? (int) max(0, (float) $height) : 0;

        return $w . ' x ' . $h . ' px';
    }
}
