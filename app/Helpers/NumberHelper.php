<?php

namespace App\Helpers;

class NumberHelper
{
    public static function numberToWords($number)
    {
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
            100000             => 'lakh',
            10000000           => 'crore'
        ];

        if (!is_numeric($number)) {
            return false;
        }

        if ($number < 0) {
            return $negative . self::numberToWords(abs($number));
        }

        $string = '';

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
                $hundreds  = $number / 100;
                $remainder = $number % 100;

                $string = $dictionary[(int)$hundreds] . ' ' . $dictionary[100];

                if ($remainder) {
                    $string .= $conjunction . self::numberToWords($remainder);
                }
                break;

            case $number < 100000:
                $thousands = $number / 1000;
                $remainder = $number % 1000;

                $string = self::numberToWords((int)$thousands) . ' ' . $dictionary[1000];

                if ($remainder) {
                    $string .= $separator . self::numberToWords($remainder);
                }
                break;

            case $number < 10000000:
                $lakhs = $number / 100000;
                $remainder = $number % 100000;

                $string = self::numberToWords((int)$lakhs) . ' ' . $dictionary[100000];

                if ($remainder) {
                    $string .= $separator . self::numberToWords($remainder);
                }
                break;

            default:
                $crores = $number / 10000000;
                $remainder = $number % 10000000;

                $string = self::numberToWords((int)$crores) . ' ' . $dictionary[10000000];

                if ($remainder) {
                    $string .= $separator . self::numberToWords($remainder);
                }
                break;
        }

        return $string;
    }
}
