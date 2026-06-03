<?php

use App\Http\Controllers\Admin\AgentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VehicleDetailsController;
use App\Http\Controllers\Admin\CrewProfilesController;
use App\Http\Controllers\Admin\VehicleAssignmentController;
use App\Http\Controllers\Admin\VehicleBookingController;
use App\Http\Controllers\Admin\GpsDashboardController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\EmailLogController;
use App\Http\Controllers\Admin\SmsLogController;
use App\Http\Controllers\Admin\EmailTemplateActivitiesController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\FuelPurchaseController;
use App\Http\Controllers\Admin\PetrolPumpController;
use App\Http\Controllers\Admin\PetrolPumpTransactionController;
use App\Http\Controllers\Admin\VehiclePermitController;
use App\Http\Controllers\Admin\VehicleRepairController;
use App\Http\Controllers\Admin\VehicleServiceController;
use App\Http\Controllers\Admin\VehicleTyreChangeController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\QuestionnaireController;
use App\Http\Controllers\Admin\VehicleMomentController;
use App\Http\Controllers\Admin\ProformaInvoiceController;
use App\Http\Controllers\Admin\ModulesController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\TripCategoryController;
use App\Http\Controllers\Admin\TripRouteController;
use App\Http\Controllers\Admin\VehicleOwnerController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BasicTableController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\SeaterController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentReceiptController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SplashScreenController;
use App\Http\Controllers\Api\EsewaPaymentController;
use App\Http\Controllers\Api\EsewaIbftController;
use App\Http\Controllers\Api\KhaltiPaymentController;
use App\Http\Controllers\Admin\ContactUsController;
use App\Http\Controllers\Admin\CrewBankDetailsController;
use App\Http\Controllers\Admin\PaymentModeController;
use App\Http\Controllers\Admin\VehicleSecurityFeatureController;
use App\Http\Controllers\Auth\ResetPasswordController;

Route::get('/', function () {
    return redirect()->route('login');
});

require __DIR__ . '/auth.php';

Route::namespace('App\Http\Controllers\Admin')->middleware(['auth', 'verified', 'gatedefine.middleware'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::prefix('dashboard')->name('admin.')->group(function () {
    Route::middleware(['auth', 'verified', 'gatedefine.middleware'])->group(function () {
        Route::get('/dashboard/data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data');
        Route::post('/ajax/customers', [CustomerController::class, 'storeAjax'])->name('ajax.customers.store');
        Route::get('/ajax/customers', [CustomerController::class, 'listAjax'])->name('ajax.customers.list');
        Route::post('/ajax/trip-categories', [TripCategoryController::class, 'storeAjax'])->name('ajax.trip-categories.store');
        Route::get('/ajax/trip-categories', [TripCategoryController::class, 'listAjax'])->name('ajax.trip-categories.list');
        Route::post('/ajax/trip-routes', [TripRouteController::class, 'storeAjax'])->name('ajax.trip-routes.store');
        Route::get('/ajax/trip-routes', [TripRouteController::class, 'listAjax'])->name('ajax.trip-routes.list');
        Route::resource('customers', CustomerController::class);
        Route::resource('vehicleowner', VehicleOwnerController::class);
        Route::prefix('user_roles')->group(function () {
            Route::controller(RolesController::class)->group(function () {
                Route::get('/', 'index')->name('user_roles.index');
                Route::get('/add', 'add')->name('user_roles.add');
                Route::post('/store', 'store')->name('user_roles.store');
                Route::get('/edit/{id}', 'edit')->name('user_roles.edit');
                Route::put('/update/{id}', 'update')->name('user_roles.update');
                Route::delete('/delete/{id}', 'delete')->name('user_roles.delete');
            });
        });
        Route::post('admin/vehicles/set-active-tab', [VehicleController::class, 'setActiveTab'])
            ->name('vehicles.set-active-tab');
        Route::resource('vehicles', VehicleController::class);
        Route::resource('users', UserController::class);
        Route::get('profile', [UserController::class, 'show'])->name('profile.show');
        Route::get('profile/edit', [UserController::class, 'editProfile'])->name('profile.edit');
        Route::post('profile/update', [UserController::class, 'updateProfile'])->name('profile.update');
        Route::post('profile/password-update', [UserController::class, 'updatePassword'])
            ->name('profile.password.update');
        Route::resource('modules', ModulesController::class);
        Route::resource('permissions', PermissionsController::class);
        Route::resource('crew_profiles', CrewProfilesController::class);
        Route::post('convert-multiple-ad-to-bs', [VehicleBookingController::class, 'convertMultipleAdToBs'])
            ->name('vehicle_bookings.convert_multiple_ad_to_bs');
        Route::post('vehicle_bookings/convert-ad-to-bs', [VehicleBookingController::class, 'convertAdtoBs'])
            ->name('vehicle_bookings.convert_ad_to_bs');

        Route::get('vehicle_bookings/export', [VehicleBookingController::class, 'export'])
            ->name('vehicle_bookings.export');

        Route::get('vehicle_bookings/events', [VehicleBookingController::class, 'fetchEvents'])
            ->name('vehicle_bookings.events');

        Route::get(
            'get-trip-routes/{category}',
            [VehicleBookingController::class, 'getRoutes']
        )->name('get_trip_routes');

        Route::get('trip-categories/list', [VehicleBookingController::class, 'getTripCategoriesList'])->name('ajax.trip-categories.list');
        Route::post('trip-categories/store', [VehicleBookingController::class, 'storeTripCategory'])->name('ajax.trip-categories.store');
        Route::get('trip-routes/list', [VehicleBookingController::class, 'getTripRoutesList'])->name('ajax.trip-routes.list');
        Route::post('trip-routes/store', [VehicleBookingController::class, 'storeTripRoute'])->name('ajax.trip-routes.store');
        Route::post('drivers/store', [VehicleBookingController::class, 'storeDriver'])->name('ajax.drivers.store');
        Route::post('helpers/store', [VehicleBookingController::class, 'storeHelper'])->name('ajax.helpers.store');

        Route::get('/vehicle-bookings/multiple/create', [VehicleBookingController::class, 'createMultiple'])->name('vehicle_bookings.multiple.create');
        Route::post('vehicle-bookings/multiple-store', [VehicleBookingController::class, 'multipleStore'])->name('vehicle_bookings.multiple.store');


        Route::get('proforma-invoices', [ProformaInvoiceController::class, 'index'])
            ->name('proforma.index');
        Route::get('receipt-invoices', [ProformaInvoiceController::class, 'indexReceipt'])
            ->name('receipt.index');
        Route::get('estimate-invoices', [ProformaInvoiceController::class, 'indexEstimate'])
            ->name('estimate.index');

        Route::get('/receipt/{id}/details', [ProformaInvoiceController::class, 'showDetailsReceipt'])->name('vehicle_receipt.details');



        Route::get('/invoice/generate/{file_no}', [ProformaInvoiceController::class, 'generateFinalInvoice']);
        Route::get('/proforma/generate/{file_no}', [ProformaInvoiceController::class, 'generateFinalProforma']);


        Route::get('/vehicle-receipt/download/{id}', [ProformaInvoiceController::class, 'downloadInvoice'])
            ->name('vehicle_receipt.download');
        Route::get('/vehicle-final-receipt/download/{id}', [ProformaInvoiceController::class, 'downloadReceipt'])
            ->name('vehicle_final_receipt.download');
        Route::get('/proforma/download/{id}', [ProformaInvoiceController::class, 'downloadProforma'])
            ->name('proforma.download');
        Route::get('/estimate/download/{id}', [ProformaInvoiceController::class, 'downloadEstimate'])
            ->name('estimate.download');



        Route::get('/invoice/single/{booking_id}', [ProformaInvoiceController::class, 'generateSingleInvoice']);
        Route::get('/vehicle-receipt/bookings/{file_no}', [ProformaInvoiceController::class, 'getBookingsByFileNo'])
            ->name('vehicle_receipt.bookings');
        Route::post('/receipt/finalize', [ProformaInvoiceController::class, 'finalizeReceipt']);


        Route::resource('vehicle_bookings', VehicleBookingController::class)
            ->parameters([
                'vehicle_bookings' => 'vehicle_booking'
            ]);

        Route::get('/gps', [GpsDashboardController::class, 'index'])->name('gpsdashboard');
        Route::resource('petrol_pumps', PetrolPumpController::class);
        Route::resource('petrol_pump_transactions', PetrolPumpTransactionController::class);

        Route::resource('questionnaires', QuestionnaireController::class);
        Route::resource('vehicle_moments', VehicleMomentController::class);
        Route::resource('fuel_purchased', FuelPurchaseController::class);



        Route::resource('emailtemplate_activities', EmailTemplateActivitiesController::class);
        Route::resource('email-templates', EmailTemplateController::class);
        Route::resource('email-logs', EmailLogController::class);
        Route::resource('sms-logs', SmsLogController::class);
        Route::resource('trip-categories', TripCategoryController::class);
        Route::get(
            'trip-routes-export',
            [TripRouteController::class, 'export']
        )->name('trip-routes.export');
        Route::get('/trip-routes/category/view', [TripRouteController::class, 'categoryView'])->name('trip-routes.category.view');
        Route::get('trip-routes-upload', [TripRouteController::class, 'upload'])->name('trip-routes.upload');
        Route::post('trip-routes-import', [TripRouteController::class, 'import'])->name('trip-routes.import');
        Route::resource('trip-routes', TripRouteController::class);
        Route::resource('vehicle-permits', VehiclePermitController::class);
        Route::resource('vehicle-services', VehicleServiceController::class);
        Route::resource('vehicle-repairs', VehicleRepairController::class);
        Route::resource('vehicle-tyre-changes', VehicleTyreChangeController::class);
        Route::resource('vendors', VendorController::class);
        Route::post('attendance/convert-ad-to-bs', [AttendanceController::class, 'convertAdToBs'])->name('attendance.convert_ad_to_bs');
        Route::post('attendance/convert-multiple-ad-to-bs', [AttendanceController::class, 'convertMultipleAdToBs'])->name('attendance.convert_multiple_ad_to_bs');
        Route::get('attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');
        Route::get('attendance/events', [AttendanceController::class, 'fetchEvents'])->name('attendance.events');

        Route::get('/attendance/create-allowance', [AttendanceController::class, 'createAllowance'])->name('attendance.createAllowance');
        Route::post('/attendance/store-allowance', [AttendanceController::class, 'storeAllowance'])->name('attendance.storeAllowance');

        Route::resource('attendance', AttendanceController::class);
        Route::resource('vehicle_assignments', VehicleAssignmentController::class);
        Route::resource('basic_tables', BasicTableController::class);
        Route::resource('brand', BrandController::class);
        Route::resource('seater', SeaterController::class);
        Route::resource('banner', BannerController::class);
        Route::resource('contact-us', ContactUsController::class);
        Route::resource('splashscreen', SplashScreenController::class);
        Route::resource('faq', FaqController::class);
        Route::resource('vehicle-security-features', VehicleSecurityFeatureController::class);
        Route::resource('agents', AgentController::class);
        Route::resource('payment-modes', PaymentModeController::class);
        Route::get('coupons/{coupon}/pdf', [CouponController::class, 'downloadPdf'])
            ->name('coupons.pdf');
        Route::post('coupons/store-from-booking', [CouponController::class, 'storeFromBooking'])
            ->name('coupons.store_from_booking');
        Route::resource('admin/coupons', CouponController::class);
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/export-pdf', [ReportController::class, 'exportPdf'])->name('export-pdf');
            Route::get('/export-excel', [ReportController::class, 'exportExcel'])->name('export-excel');
            Route::get('/export-client-report', [ReportController::class, 'exportClientReport'])->name('export-client');
        });

        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{method}/{id}', [PaymentController::class, 'show'])->name('payments.show');
        Route::delete('/payments/{method}/{id}', [PaymentController::class, 'destroy'])->name('payments.destroy');




        Route::get('crew/{crewId}/bank-details', [CrewBankDetailsController::class, 'index'])
            ->name('bank-details.index');
        Route::get('crew/{crewId}/bank-details/create', [CrewBankDetailsController::class, 'create'])
            ->name('bank-details.create');
        Route::post('crew/{crewId}/bank-details/store', [CrewBankDetailsController::class, 'store'])
            ->name('bank-details.store');
        Route::get('crew/{crewId}/bank-details/{id}/edit', [CrewBankDetailsController::class, 'edit'])
            ->name('bank-details.edit');
        Route::put('crew/{crewId}/bank-details/{id}', [CrewBankDetailsController::class, 'update'])
            ->name('bank-details.update');
        Route::delete('crew/{crewId}/bank-details/{id}', [CrewBankDetailsController::class, 'destroy'])
            ->name('bank-details.destroy');



        Route::get('/payment-receipts', [PaymentReceiptController::class, 'index'])->name('payment_receipt.index');
        Route::get('/payment-receipts/create', [PaymentReceiptController::class, 'create'])->name('payment_receipt.create');
        Route::get('/payment-receipts/show/{id}', [PaymentReceiptController::class, 'show'])->name('payment_receipt.show');
        Route::post('/payment-receipts/get-unpaid', [PaymentReceiptController::class, 'getUnpaidInvoices'])->name('payment_receipt.get_unpaid');
        Route::post('/payment-receipts/store', [PaymentReceiptController::class, 'store'])->name('payment_receipt.store');
        Route::get('/payment-receipts/download/{id}', [PaymentReceiptController::class, 'download'])->name('payment_receipt.download');
        // TDS Report Route
        Route::get('/tds-report', [PaymentReceiptController::class, 'getTDSReport'])->name('tds.report');
    });

    Route::post('/password/reset-otp/send', [ResetPasswordController::class, 'sendResetOtp'])->name('password.reset.otp.send');
    Route::post('/password/reset-otp/verify', [ResetPasswordController::class, 'resetWithOtp'])->name('password.reset.otp.verify');

    Route::middleware(['auth'])->group(function () {
        //Roles Route is here
        Route::resource('vehicle_details', VehicleDetailsController::class);

        //Route::resource('vehicle_assignments', VehicleAssignmentController::class);

        Route::get('petrol-pumps/{id}/balance', [PetrolPumpTransactionController::class, 'getPetrolPumpBalance'])
            ->name('petrol_pumps.balance');

        // Route::get('proforma-invoices', [ProformaInvoiceController::class, 'index'])
        //     ->name('proforma.index');

        Route::get('proforma-invoices/download/{id}', [ProformaInvoiceController::class, 'download'])
            ->name('proforma.download');


        Route::get('vehicle-receipt/download/{id}', [ProformaInvoiceController::class, 'downloadInvoice'])
            ->name('vehicle_receipt.download');

        Route::get(
            'vehicle-receipt/{moment}/{type}',
            [ProformaInvoiceController::class, 'generateInvoice']
        )->name('vehicle_receipt.generate');

        Route::prefix('gpsdashboard')->name('gpsdashboard.')->controller(GpsDashboardController::class)->group(function () {

            Route::get('/live-data', 'getLiveData')->name('live');

            Route::prefix('vehicle')->group(function () {
                Route::get('/{imei}', 'getVehicleDetails')->name('vehicle.details');
                Route::get('/{imei}/history', 'getVehicleHistory')->name('vehicle.history');
            });

            Route::prefix('events')->group(function () {
                Route::get('/recent', 'getRecentEvents')->name('events.recent');
            });
        });

        Route::post(
            '/attendance/khalti/initiate',
            [KhaltiPaymentController::class, 'initiateAttendancePayment']
        )->name('attendance.khalti.initiate');

        Route::get(
            '/khalti/confirm',
            [KhaltiPaymentController::class, 'confirmPayment']
        )->name('khalti.confirm');

        Route::post(
            '/manual/pay',
            [PaymentController::class, 'payManualAttendance']
        )->name('attendance.manual.pay');


        Route::post(
            '/attendance/esewa/initiate',
            [EsewaPaymentController::class, 'generateAttendanceSignature']
        )->name('attendance.esewa.initiate');

        Route::get(
            '/esewa/success',
            [EsewaPaymentController::class, 'success']
        )->name('esewa.success');


        Route::post('bank-details/validate', [EsewaIbftController::class, 'validateBankAccount'])
            ->name('bank-details.validate');

        Route::post(
            '/attendance/esewa/transfer-dashboard',
            [EsewaIbftController::class, 'transferDashboard']
        )->name('attendance.esewa.transfer-dashboard');

        Route::get(
            '/attendance/esewa/get-bank-details',
            [EsewaIbftController::class, 'getBankDetails']
        )->name('esewa.get_bank_details');
    });
});
