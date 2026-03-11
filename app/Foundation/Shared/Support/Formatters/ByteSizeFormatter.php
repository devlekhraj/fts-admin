<?php

declare(strict_types=1);

namespace App\Foundation\Shared\Support\Formatters;

final class ByteSizeFormatter
{
    public static function format(int|float|string|null $bytes): string
    {
        $size = is_numeric($bytes) ? max(0, (float) $bytes) : 0.0;
        if ($size < 1024) {
            return (string) ((int) $size) . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $index = -1;
        while ($size >= 1024 && $index < count($units) - 1) {
            $size /= 1024;
            $index++;
        }

        $formatted = number_format($size, $size >= 10 ? 1 : 2, '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');

        return $formatted . ' ' . $units[$index];
    }
}
