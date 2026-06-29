<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\VehicleBooking;
use Illuminate\Support\Facades\Log;

class BookingHelper
{
    /**
     * Check if menu or any of its children is active
     *
     * @param array $item
     * @return bool
     */

    public static function cancelExpiredBookings()
    {
        Log::info("Vehicle Cancelled Cron Job Started at " . Carbon::now());
        return VehicleBooking::where('status', 'pending')
            ->where('start_datetime', '<', Carbon::now())
            ->update([
                'status' => 'cancelled',
                'updated_at' => now(),
            ]);
        // Log::info("vehicle Cancelled crone jon done at " . Carbon::now());
    }
}
