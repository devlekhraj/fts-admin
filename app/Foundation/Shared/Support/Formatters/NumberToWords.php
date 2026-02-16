<?php

namespace App\Foundation\Shared\Support\Formatters;

final class NumberToWords
{
    public static function npr(int|float|string $number): string
    {
        if ($number == 0) {
            return '-';
        }
        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = [
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty',
            30                  => 'thirty',
            40                  => 'forty',
            50                  => 'fifty',
            60                  => 'sixty',
            70                  => 'seventy',
            80                  => 'eighty',
            90                  => 'ninety',
            100                 => 'hundred',
            1000                => 'thousand',
            100000              => 'lakh',
            10000000            => 'crore'
        ];

        if (!is_numeric($number)) {
            return '-';
        }

        if ($number < 0) {
            return $negative . self::npr(abs($number));
        }

        $string = $fraction = null;

        if (strpos((string) $number, '.') !== false) {
            [$number, $fraction] = explode('.', (string) $number);
        }

        $number = (int) $number;

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = (int) ($number / 100);
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . self::npr($remainder);
                }
                break;
            case $number < 100000:
                $thousands  = (int) ($number / 1000);
                $remainder  = $number % 1000;
                $string = self::npr($thousands) . ' ' . $dictionary[1000];
                if ($remainder) {
                    $string .= $separator . self::npr($remainder);
                }
                break;
            case $number < 10000000:
                $lakhs   = (int) ($number / 100000);
                $remainder = $number % 100000;
                $string = self::npr($lakhs) . ' ' . $dictionary[100000];
                if ($remainder) {
                    $string .= $separator . self::npr($remainder);
                }
                break;
            default:
                $crores   = (int) ($number / 10000000);
                $remainder = $number % 10000000;
                $string = self::npr($crores) . ' ' . $dictionary[10000000];
                if ($remainder) {
                    $string .= $separator . self::npr($remainder);
                }
                break;
        }

        if ($fraction !== null && is_numeric($fraction)) {
            $string .= $decimal;
            $words = [];
            foreach (str_split((string) $fraction) as $digit) {
                $words[] = $dictionary[$digit];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }
}
