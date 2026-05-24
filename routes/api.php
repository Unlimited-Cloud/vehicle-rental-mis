<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VehicleMomentController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\EsewaPaymentController;
use App\Http\Controllers\Api\KhaltiPaymentController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\EsewaIbftController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/get-token', [LoginController::class, 'getToken']);

//Driver API
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);

    Route::prefix('vehicle-moments')->group(function () {
        Route::get('/getallmoments', [VehicleMomentController::class, 'index']);
        Route::post('/createmoments', [VehicleMomentController::class, 'store']);
        Route::get('getonemoments/{id}', [VehicleMomentController::class, 'show']);
        Route::put('{id}', [VehicleMomentController::class, 'update']);
        Route::delete('{id}', [VehicleMomentController::class, 'destroy']);

        Route::get('/helpers', [VehicleMomentController::class, 'getHelpers']);
        Route::get('/driver-bookings/{driverId}', [VehicleMomentController::class, 'getDriverBookings']);

        Route::get('questionnaires', [VehicleMomentController::class, 'getAllQuestionnaire']);
        Route::get('questionnaires/{id}', [VehicleMomentController::class, 'getQuestionnaire']);
    });
});



//Customer API
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('customer')->group(function () {
        Route::controller(CustomerController::class)->group(function () {
            Route::post('/register', 'register');
            Route::post('/update-profile', 'updateProfile');
            Route::post('/logout', 'logout');
            Route::get('/profile/{customerUuid}', 'getProfileByUuid');
            Route::post('/forgot-password',  'forgotPassword');
            Route::post('/verify-otp-password',  'verifyOtp');
            Route::post('/reset-password',  'resetPassword');
            Route::post('/change-password',  'changePassword');

            Route::post('/profile-image/{customer_id}', 'updateProfileImage');
        });

        Route::controller(AuthController::class)->group(function () {
            Route::post('/request-otp-login', 'getOtpPasscodeAppLogin');
            Route::post('/login', 'login');
        });

        Route::get('/get-customerbooking/{customerUUId}', [BookingController::class, 'getCustomerBookings']);
        Route::get('/get-vehicle-drivers/{vehicleId}', [BookingController::class, 'getVehicleDrivers']);


        Route::get('/get-category', [BookingController::class, 'tripcategory']);
        Route::get('/get-routes/{category_id}', [BookingController::class, 'tripRoutes']);
        Route::post('/get-trip-price', [BookingController::class, 'getTripPrice']);
        Route::post('/bookings', [BookingController::class, 'createBooking']);
        Route::post('/vehicle-booking-import', [BookingController::class, 'importBooking']);
    });
    Route::get('/get-vehicle', [BookingController::class, 'GetVehicle']);
    Route::get('/get-drivers', [BookingController::class, 'getDrivers']);
    Route::get('/get-helpers', [BookingController::class, 'getHelpers']);
    Route::get('/brands', [BookingController::class, 'brands']);
    Route::get('/vehicles-and-brand', [BookingController::class, 'BrandWithVehicle']);
    Route::post('/vehicles-by-brand', [BookingController::class, 'vehiclesByBrand']);

    //transmission
    Route::get('/transmission', [BookingController::class, 'transmission']);
    Route::post('/vehicles-by-transmission', [BookingController::class, 'vehiclesByTransmission']);


    //seaters
    Route::get('/seaters', [BookingController::class, 'seaters']);
    Route::post('/vehicles-by-seater', [BookingController::class, 'vehiclesBySeaters']);

    Route::get('/popular-vehicles', [BookingController::class, 'mostPopularVehicles']);

    //addres
    Route::get('/province', [BookingController::class, 'provinces']);
    Route::post('/districts-by-province', [BookingController::class, 'districtsByProvince']);
    Route::post('/vdcs-by-district', [BookingController::class, 'vdcsByDistrict']);

    Route::get('/vehicle/{id}', [BookingController::class, 'VehicleDetailById']);

    Route::post('reviews', [VehicleController::class, 'storeReview']);
    Route::get('vehicles/{vehicle_id}/reviews', [VehicleController::class, 'getReviews']);
    Route::get('banners', [VehicleController::class, 'getBanner']);
    Route::get('search-vehicles', [VehicleController::class, 'SearchVehicle']);
    Route::get('booking-by-status/{status}/{customer_id}', [BookingController::class, 'BookingbyStatus']);
    Route::get('basic-setting', [BookingController::class, 'getBasicSetting']);

    Route::get(
        '/vehicles/{vehicle_id}/insurance-download',
        [VehicleController::class, 'downloadInsuranceDocument']
    );
    Route::post(
        '/booking/khalti/initiate',
        [KhaltiPaymentController::class, 'initiatePayment']
    )->name('booking.khalti.initiate');

    Route::post(
        '/booking/esewa/initiate',
        [EsewaPaymentController::class, 'generateSignature']
    )->name('booking.esewa.initiate');
});
Route::get(
    '/khalti/confirm',
    [KhaltiPaymentController::class, 'confirmPayment']
)->name('khalti.confirm');

Route::get(
    '/esewa/success',
    [EsewaPaymentController::class, 'success']
)->name('esewa.success');

Route::get('/splashscreens', [BookingController::class, 'splashscreens']);

Route::post('/invoice/generate', [BookingController::class, 'apiGenerateInvoice']);
Route::post('/invoice/regenerate', [BookingController::class, 'apiRegenerateInvoice']);

Route::post('/proforma/generate', [BookingController::class, 'apiGenerateProforma']);
Route::post('/proforma/regenerate', [BookingController::class, 'apiRegenerateProforma']);

Route::post('/estimate/generate', [BookingController::class, 'apiGenerateEstimate']);
Route::post('/estimate/regenerate', [BookingController::class, 'apiRegenerateEstimate']);

Route::post('/prof-invoice', [VehicleMomentController::class, 'generateFromBooking']);
Route::get('/basic-setup', [CustomerController::class, 'BasicSetup']);
Route::get('/faq', [VehicleController::class, 'faq']);
Route::get('/contact-us', [BookingController::class, 'contactus']);

Route::post('/customer-location', [BookingController::class, 'storeLatLng']);
Route::get('/customer-location/{customer_uuid}', [BookingController::class, 'showLatlng']);

Route::get('/payment-modes', [BookingController::class, 'paymentModes']);



Route::prefix('esewa')->group(function () {
    Route::controller(EsewaIbftController::class)->group(function () {
        Route::get('get-banks', 'getBanks');
        Route::post('validate-account', 'validateAccount');
        Route::post('send-direct-transaction', 'transfer');
    });
});


Route::get('/test-hmac', function () {
    $key     = 'esewa';
    $account = '1234567891011120';
    $swift   = 'PRVUNPKA';
    $name    = 'PRATIMA KUMARI';
    $target  = '483460a01098ffaa8655c98aea1e7e83178577eddd655dcd5780037dce569172';

    $tests = [
        "$account,$swift,$name"  => hash_hmac('sha256', "$account,$swift,$name", $key),
        "$account,$name,$swift"  => hash_hmac('sha256', "$account,$name,$swift", $key),
        "$name,$account,$swift"  => hash_hmac('sha256', "$name,$account,$swift", $key),
        "$swift,$account,$name"  => hash_hmac('sha256', "$swift,$account,$name", $key),
        "$name,$swift,$account"  => hash_hmac('sha256', "$name,$swift,$account", $key),
        "$swift,$name,$account"  => hash_hmac('sha256', "$swift,$name,$account", $key),
        // without comma
        "$account$swift$name"    => hash_hmac('sha256', "$account$swift$name", $key),
        // pipe separator
        "$account|$swift|$name"  => hash_hmac('sha256', "$account|$swift|$name", $key),
    ];

    foreach ($tests as $msg => $hash) {
        $match = $hash === $target ? ' ✅ MATCH' : '';
        dump("$msg => $hash$match");
    }
});

Route::get('/test-txn-hmac3', function () {
    $key      = 'esewa';
    $target   = 'edf7cf6c4aad5c3c0c41cdcc0fbe2b44b81e88cb213304f7e00fd3f2875165bc';
    $srcBank  = 'NARBNPKA';
    $srcAcc   = '9100100008977000001';
    $srcName  = 'Test CE';
    $dstBank  = 'PRVUNPKA';
    $dstAcc   = '1234567891011120';
    $dstName  = 'PRATIMA KUMARI';
    $amount   = '100.00';
    $uniqueId = 'UNIQueID123A';
    $clientId = 'CP0006002';

    // validate pattern was: swift, account, name
    // so transaction pattern might be: dst_bank, dst_acc, dst_name, src_bank, src_acc, src_name, amount, unique_id
    $tests = [
        // mirror of validate: bank, account, name pattern
        "$dstBank,$dstAcc,$dstName,$srcBank,$srcAcc,$srcName,$amount,$uniqueId",
        "$srcBank,$srcAcc,$srcName,$dstBank,$dstAcc,$dstName,$amount,$uniqueId",

        // bank, account, name but flipped order
        "$dstBank,$dstAcc,$dstName,$amount,$uniqueId",
        "$srcBank,$srcAcc,$srcName,$amount,$uniqueId",

        // just bank + account like validate used swift + account
        "$dstBank,$dstAcc,$amount,$uniqueId",
        "$srcBank,$srcAcc,$amount,$uniqueId",

        // dst then src (bank+acc only)
        "$dstBank,$dstAcc,$srcBank,$srcAcc,$amount,$uniqueId",
        "$srcBank,$srcAcc,$dstBank,$dstAcc,$amount,$uniqueId",

        // with client_id prefixed like validate had swift first
        "$clientId,$dstBank,$dstAcc,$srcBank,$srcAcc,$amount,$uniqueId",
        "$clientId,$srcBank,$srcAcc,$dstBank,$dstAcc,$amount,$uniqueId",

        // name only combos (validate had name as 3rd field)
        "$dstBank,$dstAcc,$dstName,$amount,$uniqueId",
        "$srcBank,$srcAcc,$srcName,$dstBank,$dstAcc,$dstName,$amount,$uniqueId",

        // unique_id last vs first
        "$dstBank,$dstAcc,$dstName,$srcBank,$srcAcc,$srcName,$uniqueId,$amount",
        "$uniqueId,$dstBank,$dstAcc,$dstName,$srcBank,$srcAcc,$srcName,$amount",
    ];

    foreach ($tests as $msg) {
        $hash  = hash_hmac('sha256', $msg, $key);
        $match = $hash === $target ? ' ✅ MATCH' : '';
        dump("$msg => $hash$match");
    }
});
