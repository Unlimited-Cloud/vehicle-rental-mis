<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Log;

class NepaliDateHelper
{
    /**
     * Convert English date to Nepali using Hamro Patro API
     */
    public static function convertToNepali($englishDate)
    {
        try {

            $response = Http::asForm()->post(
                'https://www.hamropatro.com/getMethod.php',
                [
                    'actionName'     => 'wdconverter',
                    'datefield'      => $englishDate,
                    'convert_option' => 'eng_to_nep',
                ]
            );

            if ($response->successful()) {

                // Example response:
                // "March, 01 2026 | <span>२०८२ फागुन १७</span>"

                $raw = trim($response->body());

                // 🔥 Step 1: Split by |
                if (str_contains($raw, '|')) {
                    $parts = explode('|', $raw);
                    $nepaliPart = trim($parts[1]);
                } else {
                    $nepaliPart = $raw;
                }

                // 🔥 Step 2: Remove HTML tags completely
                $nepaliPart = strip_tags($nepaliPart);

                // Now we have:
                // "२०८२ फागुन १७"

                $segments = explode(' ', $nepaliPart);

                return [
                    'nepali' => $nepaliPart,
                    'year'   => $segments[0] ?? '',
                    'month'  => $segments[1] ?? '',
                    'day'    => $segments[2] ?? '',
                ];
            }
        } catch (\Exception $e) {
            Log::error('Nepali date conversion failed: ' . $e->getMessage());
        }

        return [
            'nepali' => '',
            'year'   => '',
            'month'  => '',
            'day'    => '',
        ];
    }


    /**
     * Get Nepali month name from month number
     */
    private static function getNepaliMonthName($month)
    {
        $months = [
            '01' => 'बैशाख',
            '02' => 'जेठ',
            '03' => 'असार',
            '04' => 'साउन',
            '05' => 'भदौ',
            '06' => 'असोज',
            '07' => 'कात्तिक',
            '08' => 'मंसिर',
            '09' => 'पुस',
            '10' => 'माघ',
            '11' => 'फागुन',
            '12' => 'चैत'
        ];

        return $months[$month] ?? 'बैशाख';
    }

    /**
     * Convert English number to Nepali Unicode
     */
    public static function toNepaliNumber($number)
    {
        $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $nepaliNumbers = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];

        return str_replace($englishNumbers, $nepaliNumbers, (string)$number);
    }
}
