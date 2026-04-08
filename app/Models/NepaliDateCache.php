<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NepaliDateCache extends Model
{
    protected $table = 'nepali_date_cache';

    protected $fillable = [
        'english_date',
        'nepali_date',
        'nepali_year',
        'nepali_month',
        'nepali_day',
        'nepali_month_name'
    ];

    protected $casts = [
        'english_date' => 'date'
    ];

    // Cache for a specific year
    public static function getYearlyCache($year)
    {
        return self::whereYear('english_date', $year)
            ->get()
            ->keyBy(function ($item) {
                return $item->english_date->format('Y-m-d');
            });
    }

    // Check if date exists in cache
    public static function hasDate($date)
    {
        return self::where('english_date', $date)->exists();
    }

    // Get cached date
    public static function getCachedDate($date)
    {
        return self::where('english_date', $date)->first();
    }

    // Store multiple dates at once
    public static function storeBulk($dates)
    {
        return self::insert($dates);
    }
}
