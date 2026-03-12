<?php

namespace App\Utilities;

use Ramsey\Uuid\Uuid;
use GuzzleHttp\Client;
use App\Helpers\AccesstokenHelper;
use App\Models\Customer;
use App\Models\Passcode;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Partnerdetailstable;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Calculation\Token\Stack;
use Illuminate\Support\Facades\Http;
use Stevebauman\Location\Facades\Location;
use App\Helpers\PasscodeHelper;

class VehiclerentalUtilities
{
    protected $client;


    public static function searchForEmailVar($emailContent, $personalDetails, $passcode = null)
    {

        return  preg_replace_callback('/#(\w+)#/', function ($matches) use ($personalDetails, $passcode) {
            $varName = $matches[1];


            if ($varName === 'month') {
                $Months = [
                    "1" => "January",
                    "2" => "February",
                    "3" => "March",
                    "4" => "April",
                    "5" => "May",
                    "6" => "June",
                    "7" => "July",
                    "8" => "August",
                    "9" => "September",
                    "10" => "October",
                    "11" => "November",
                    "12" => "December"
                ];

                if (isset($personalDetails[$varName])) {
                    $monthVal = ltrim($personalDetails[$varName], '0');
                    return $Months[$monthVal] ?? $personalDetails[$varName];
                }
            }
            $value = is_array($personalDetails)
                ? (isset($personalDetails[$varName]) ? $personalDetails[$varName] : null)
                : (isset($personalDetails->$varName) ? $personalDetails->$varName : null);

            if ($value !== null) {
                return $value;
            }

            if ($passcode && isset($passcode[$varName])) {
                return $passcode[$varName];
            }

            // Special case for #now#
            if ($varName === 'now') {
                //$currentDateTimeLocal = date('Y-m-d H:i:s');
                //$localTime = now()->setTimezone('Asia/Kathmandu')->format('Y-m-d H:i:s');
                //return $localTime;
                return now()->format('Y-m-d H:i:s') . ' (' . date_default_timezone_get() . ' TIME)'; // Adjust format as needed
            }
        }, $emailContent);
    }

    /**
     * @author prabinunlimited
     * Convert date to Y-m-d H:i:s
     * @return date
     */
    public static function covertDateToYmdhis($date)
    {
        return Carbon::parse($date)->format('Y-m-d H:i:s');
    }

    /**
     * @author prabinunlimited
     * Convert date to Y-m-d
     * @return date
     */
    public static function covertDateToYmd($date)
    {
        return Carbon::parse($date)->format('Y-m-d');
    }
}
