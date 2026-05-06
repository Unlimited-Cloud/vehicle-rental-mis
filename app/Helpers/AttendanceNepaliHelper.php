<?php
// app/Helpers/AttendanceNepaliHelper.php

namespace App\Helpers;

use App\Models\NepaliDateCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AttendanceNepaliHelper
{
    /**
     * Convert English date to Nepali using database cache first, then API
     * Returns format with 'display' key for attendance system
     */
    public static function convertToNepali($englishDate)
    {
        try {
            // Format date
            if ($englishDate instanceof Carbon) {
                $formattedDate = $englishDate->format('Y-m-d');
                $date = $englishDate;
            } else {
                $date = Carbon::parse($englishDate);
                $formattedDate = $date->format('Y-m-d');
            }

            // Check Laravel cache first (for performance)
            $cacheKey = 'attendance_nepali_date_' . $formattedDate;

            return Cache::remember($cacheKey, now()->addDays(30), function () use ($formattedDate, $date) {
                // Check database cache
                $cached = NepaliDateCache::where('english_date', $formattedDate)->first();

                if ($cached) {
                    // Create display format
                    $display = "{$cached->nepali_year} {$cached->nepali_month} {$cached->nepali_day}";
                    $englishDisplay = $date->format('F j, Y');

                    return [
                        'success' => true,
                        'display' => $display,
                        'nepali' => $display,
                        'english' => $englishDisplay,
                        'year' => $cached->nepali_year,
                        'month' => $cached->nepali_month,
                        'day' => $cached->nepali_day,
                        'year_num' => self::toEnglishNumber($cached->nepali_year),
                        'month_num' => self::getMonthNumber($cached->nepali_month),
                        'day_num' => self::toEnglishNumber($cached->nepali_day),
                    ];
                }

                // Not in database, call API and save
                return self::fetchFromApi($formattedDate, $date);
            });
        } catch (\Exception $e) {
            Log::error('Attendance Nepali date conversion failed: ' . $e->getMessage());
            return self::getFallbackDate($englishDate);
        }
    }

    /**
     * Fetch from API and save to database
     */
    private static function fetchFromApi($formattedDate, $date)
    {
        try {
            $response = Http::asForm()->post(
                'https://www.hamropatro.com/getMethod.php',
                [
                    'actionName'     => 'wdconverter',
                    'datefield'      => $formattedDate,
                    'convert_option' => 'eng_to_nep',
                ]
            );

            if ($response->successful()) {
                $raw = trim($response->body());

                // Split by | to get English and Nepali parts
                if (str_contains($raw, '|')) {
                    $parts = explode('|', $raw);
                    $englishPart = trim($parts[0]);
                    $nepaliPart = trim($parts[1]);
                } else {
                    $englishPart = $raw;
                    $nepaliPart = $raw;
                }

                // Remove HTML tags from Nepali part
                $nepaliPart = strip_tags($nepaliPart);
                $nepaliPart = preg_replace('/\s+/', ' ', $nepaliPart);
                $nepaliPart = trim($nepaliPart);

                // Parse the Nepali date (format: "२०८२ फागुन १७")
                $segments = explode(' ', $nepaliPart);
                $year = $segments[0] ?? '';
                $month = $segments[1] ?? '';
                $day = $segments[2] ?? '';

                // Create display format with both English and Nepali (for tooltips)
                $display = $nepaliPart; // Just Nepali date for display
                $englishDisplay = $date->format('F j, Y');

                // Save to database for future use
                try {
                    NepaliDateCache::updateOrCreate(
                        ['english_date' => $formattedDate],
                        [
                            'nepali_date' => $nepaliPart,
                            'nepali_year' => $year,
                            'nepali_month' => $month,
                            'nepali_day' => $day,
                            'nepali_month_name' => $month,
                        ]
                    );
                    Log::info('Nepali date saved to database: ' . $formattedDate);
                } catch (\Exception $e) {
                    Log::warning('Failed to save Nepali date to database: ' . $e->getMessage());
                }

                return [
                    'success' => true,
                    'display' => $display,
                    'nepali' => $nepaliPart,
                    'english' => $englishDisplay,
                    'year' => $year,
                    'month' => $month,
                    'day' => $day,
                    'year_num' => self::toEnglishNumber($year),
                    'month_num' => self::getMonthNumber($month),
                    'day_num' => self::toEnglishNumber($day),
                ];
            }

            return self::getFallbackDate($formattedDate);
        } catch (\Exception $e) {
            Log::error('API fetch failed: ' . $e->getMessage());
            return self::getFallbackDate($formattedDate);
        }
    }

    /**
     * Convert multiple dates at once
     */
    public static function convertMultiple($dates)
    {
        $results = [];
        foreach ($dates as $date) {
            $results[$date] = self::convertToNepali($date);
        }
        return $results;
    }

    /**
     * Get fallback date if API fails
     */
    private static function getFallbackDate($englishDate)
    {
        try {
            $date = Carbon::parse($englishDate);
            $year = $date->year + 57;
            $month = $date->month + 8;
            $day = $date->day;

            if ($month > 12) {
                $month -= 12;
                $year += 1;
            }

            $monthNames = [
                1 => 'वैशाख',
                2 => 'जेठ',
                3 => 'असार',
                4 => 'साउन',
                5 => 'भदौ',
                6 => 'असोज',
                7 => 'कात्तिक',
                8 => 'मंसिर',
                9 => 'पुस',
                10 => 'माघ',
                11 => 'फागुन',
                12 => 'चैत'
            ];

            $monthName = $monthNames[$month] ?? 'बैशाख';
            $yearStr = self::toNepaliNumber($year);
            $dayStr = self::toNepaliNumber($day);

            $nepaliDate = "{$yearStr} {$monthName} {$dayStr}";

            return [
                'success' => true,
                'display' => $nepaliDate,
                'nepali' => $nepaliDate,
                'english' => $date->format('F j, Y'),
                'year' => $yearStr,
                'month' => $monthName,
                'day' => $dayStr,
                'year_num' => $year,
                'month_num' => $month,
                'day_num' => $day,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'display' => $englishDate,
                'nepali' => $englishDate,
                'english' => $englishDate,
                'year' => '',
                'month' => '',
                'day' => '',
                'year_num' => 0,
                'month_num' => 0,
                'day_num' => 0,
            ];
        }
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

    /**
     * Convert Nepali number to English
     */
    public static function toEnglishNumber($nepaliNumber)
    {
        $nepaliNumbers = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];
        $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($nepaliNumbers, $englishNumbers, (string)$nepaliNumber);
    }

    /**
     * Get month number from Nepali month name
     */
    private static function getMonthNumber($monthName)
    {
        $months = [
            'वैशाख' => 1,
            'जेठ' => 2,
            'असार' => 3,
            'साउन' => 4,
            'भदौ' => 5,
            'असोज' => 6,
            'कात्तिक' => 7,
            'मंसिर' => 8,
            'पुस' => 9,
            'माघ' => 10,
            'फागुन' => 11,
            'चैत' => 12,
        ];

        return $months[$monthName] ?? 0;
    }
}
