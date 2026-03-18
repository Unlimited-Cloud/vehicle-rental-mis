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
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Calculation\Token\Stack;
use Illuminate\Support\Facades\Http;
use Stevebauman\Location\Facades\Location;
use App\Helpers\PasscodeHelper;
use App\Models\ClientSecret;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class VehicleRentalUtilities
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

    public static function jsonResponse($data){
        return response()->json([
            'status' => $data['status'] ?? 'error',
            'message' => $data['message'] ?? 'Something went wrong',
            'data' => $data['data'] ?? [],
        ], $data['statusCode'] ?? 500);
    }

    public static function getToken($request){
        try{
            $validator = Validator::make($request->all(), [
                'client_name' => 'required|exists:client_secrets,client_name',
                'client_id' => 'required|exists:client_secrets,client_id',
                'client_secret' => 'required|exists:client_secrets,client_secret',
            ]);

            if ($validator->fails()) {
                return array(
                    'status' => 'error',
                    'message' => $validator->errors(),
                    'data' => '', 
                    'statusCode' => 422
                );
            }

            $clientDetail = ClientSecret::where('client_id',$request->client_id)->first();
            $token = $clientDetail->createToken('client-token')->plainTextToken;
            $explodeToken = explode('|',$token);

            return array(
                'status' => 'success',
                'message' => 'Token Generated Successfully!',
                'data' => ['token' => $explodeToken[1]], 
                'statusCode' => 200
            );
        }catch ( \Exception $e){
            return array(
                'status' => 'error',
                'message' => 'Internal Server Error',
                'data' => '', 
                'statusCode' => 500
            );
        }
    }
}
