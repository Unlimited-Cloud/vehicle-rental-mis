<?php

use App\Http\Controllers\Admin\TripRouteController;
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
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\GpsController;

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
            Route::post('/delete-account',  'deleteAccount');

            Route::post('/profile-image/{customer_id}', 'updateProfileImage');
        });

        Route::controller(AuthController::class)->group(function () {
            Route::post('/request-otp-login', 'getOtpPasscodeAppLogin');
            Route::post('/resend-otp-passcode', 'resendOtpPasscode');
            Route::post('/login', 'login');
        });

        Route::get('/get-customerbooking/{customerUUId}', [BookingController::class, 'getCustomerBookings']);
        Route::get('/get-vehicle-drivers/{vehicleId}', [BookingController::class, 'getVehicleDrivers']);


        Route::get('/get-category', [BookingController::class, 'tripcategory']);
        Route::get('/get-routes/{category_id}', [BookingController::class, 'tripRoutes']);
        Route::post('/get-trip-price', [BookingController::class, 'getTripPrice']);
        Route::post('/get-trip-price-new', [BookingController::class, 'getTripPriceNew']);
        Route::post('/bookings', [BookingController::class, 'createBooking']);
        Route::post('/vehicle-booking-import', [BookingController::class, 'importBooking']);
    });
    Route::get('/get-vehicle', [BookingController::class, 'GetVehicle']);
    Route::get('/get-drivers', [BookingController::class, 'getDrivers']);
    Route::get('/get-helpers', [BookingController::class, 'getHelpers']);
    Route::get('/brands', [BookingController::class, 'brands']);
    Route::get('/vehicles-and-brand', [BookingController::class, 'BrandWithVehicle']);
    Route::post('/vehicles-by-brand', [BookingController::class, 'vehiclesByBrand']);


    Route::post('/check-vehicle-availability', [BookingController::class, 'checkAvailability']);


    //transmission
    Route::get('/transmission', [BookingController::class, 'transmission']);
    Route::post('/vehicles-by-transmission', [BookingController::class, 'vehiclesByTransmission']);


    //seaters
    Route::get('/seaters', [BookingController::class, 'seaters']);
    Route::post('/vehicles-by-seater', [BookingController::class, 'vehiclesBySeaters']);

    Route::get('/popular-vehicles', [BookingController::class, 'mostPopularVehicles']);

    //addres
    Route::controller(ResourceController::class)->group(function () {
        Route::get('/countries', 'getCountries');
        Route::get('/province', 'provinces');
        Route::post('/districts-by-province', 'districtsByProvince');
        Route::post('/vdcs-by-district', 'vdcsByDistrict');
    });

    Route::get('/vehicle/{id}', [BookingController::class, 'VehicleDetailById']);

    Route::post('reviews', [VehicleController::class, 'storeReview']);
    Route::get('vehicles/{vehicle_id}/reviews', [VehicleController::class, 'getReviews']);
    Route::get('vehicles/{vehicle_id}/limit-reviews', [VehicleController::class, 'getLimitReviews']);
    Route::get('banners', [VehicleController::class, 'getBanner']);
    Route::get('search-vehicles', [VehicleController::class, 'SearchVehicle']);
    Route::get('booking-by-status/{status}/{customer_id}', [BookingController::class, 'BookingbyStatus']);
    Route::get('booking-by-all-status/{customer_id}', [BookingController::class, 'BookingbyAllStatus']);


    Route::get('vehicle-bookings/{vehicle_id}', [BookingController::class, 'vehicleBookings']);
    Route::get('/booking-details/{booking_id}', [BookingController::class, 'bookingDetails']);
    Route::get('/booking-log-details/{booking_id}', [BookingController::class, 'bookingLogDetails']);
    Route::get('completed-vehicle-bookings/{vehicle_id}', [BookingController::class, 'completedVehicleBookings']);
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

    Route::post(
        '/booking/cash/inititate',
        [BookingController::class, 'codPayment']
    );

    Route::post('/complete-cod-payment', [BookingController::class, 'completeCodPayment']);


    Route::prefix('gps')->group(function () {
        Route::post('/add-user', [GpsController::class, 'addUser']);
        Route::post('/add-vehicle', [GpsController::class, 'addVehicle']);
        Route::post('/assign-vehicle', [GpsController::class, 'assignVehicle']);
        //users
        Route::post('/location', [GpsController::class, 'getLocation']);
        Route::post('/route', [GpsController::class, 'getRoute']);
        Route::get('/get-address', [GpsController::class, 'getAddress']);
        Route::post('/objects', [GpsController::class, 'getUserObjects']);
        Route::get('/object-commands', [GpsController::class, 'getObjectCommands']);
        Route::get('/messages', [GpsController::class, 'getMessages']);
        Route::get('/events', [GpsController::class, 'getEvents']);
        Route::get('/last-events', [GpsController::class, 'getLastEvents']);
        Route::get('/markers', [GpsController::class, 'getMarkers']);
        Route::get('/saved-routes', [GpsController::class, 'getSavedRoutes']);
        Route::get('/zones', [GpsController::class, 'getZones']);

        Route::post('/send-gprs', [GpsController::class, 'sendGprs']);
        Route::post('/send-sms', [GpsController::class, 'sendSms']);
        Route::get('/fleet-locations', [GpsController::class, 'getFleetLocations']);
        Route::post('/nearest-fleet', [GpsController::class, 'nearestFleet']);
        Route::post('/vehicle-distance-from-gps', [GpsController::class, 'vehicleDistanceFromGps']);
    });
});
Route::post('/complete-cod-payment-dashboard', [BookingController::class, 'completeCodPayment']);
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

Route::get('/vehicle-receipt/{booking_id}', [BookingController::class, 'getReceipt']);
Route::get('/vehicle-receipt-blob/{booking_id}', [BookingController::class, 'getReceiptBlob']);

Route::get('/vehicle-sorting', [BookingController::class, 'vehicleSorting']);

Route::post('/import-route-price', [TripRouteController::class, 'importRoutePrice']);


Route::prefix('esewa')->group(function () {
    Route::controller(EsewaIbftController::class)->group(function () {
        Route::get('get-banks', 'getBanks');
        Route::post('validate-account', 'validateAccount');
        Route::post('send-direct-transaction', 'transfer');
        Route::post('transaction-status',  'getTransactionStatus');
        Route::post('transaction-report',  'getTransactionReport');
    });
});
