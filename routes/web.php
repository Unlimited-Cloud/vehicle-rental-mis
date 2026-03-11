<?php

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
use App\Http\Controllers\Admin\PetrolPumpController;
use App\Http\Controllers\Admin\PetrolPumpTransactionController;
use App\Http\Controllers\Admin\VehiclePermitController;
use App\Http\Controllers\Admin\VehicleRepairController;
use App\Http\Controllers\Admin\VehicleServiceController;
use App\Http\Controllers\Admin\VehicleTyreChangeController;
use App\Http\Controllers\Admin\RolesController;


// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', function () {
    return redirect()->route('login');
});

require __DIR__ . '/auth.php';


// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::namespace('App\Http\Controllers\Admin')->middleware(['auth', 'verified', 'gatedefine.middleware'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});

Route::namespace('App\Http\Controllers\Admin')->middleware(['auth', 'verified', 'gatedefine.middleware'])->prefix('dashboard')->name('admin.')->group(function () {
    Route::resource('customers', CustomerController::class);
});

Route::namespace('App\Http\Controllers\Admin')->middleware(['auth', 'verified', 'gatedefine.middleware'])->prefix('dashboard')->name('admin.')->group(function () {
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
});

Route::namespace('App\Http\Controllers\Admin')->middleware(['auth'])->prefix('dashboard')->name('admin.')->group(function () {
    //Roles Route is here
    
    Route::resource('vehicles', VehicleController::class);
    Route::resource('users', UserController::class);
    Route::resource('vehicle_details', VehicleDetailsController::class);
    Route::resource('crew_profiles', CrewProfilesController::class);
    Route::resource('vehicle_assignments', VehicleAssignmentController::class);

    Route::get('vehicle_bookings/export', [VehicleBookingController::class, 'export'])
        ->name('vehicle_bookings.export');

    Route::get('vehicle_bookings/events', [VehicleBookingController::class, 'fetchEvents'])
        ->name('vehicle_bookings.events');

    Route::resource('vehicle_bookings', VehicleBookingController::class)
        ->parameters([
            'vehicle_bookings' => 'vehicle_booking'
        ]);

    Route::post('admin/vehicles/set-active-tab', [VehicleController::class, 'setActiveTab'])
        ->name('vehicles.set-active-tab');

    Route::resource('vehicle-permits', VehiclePermitController::class);
    Route::resource('vehicle-services', VehicleServiceController::class);
    Route::resource('vehicle-repairs', VehicleRepairController::class);
    Route::resource('vehicle-tyre-changes', VehicleTyreChangeController::class);

    
    Route::resource('petrol_pumps', PetrolPumpController::class);
    Route::resource('petrol_pump_transactions', PetrolPumpTransactionController::class);
    Route::get('petrol-pumps/{id}/balance', [PetrolPumpTransactionController::class, 'getPetrolPumpBalance'])
        ->name('petrol_pumps.balance');


    Route::get('/gps', [GpsDashboardController::class, 'index'])->name('gpsdashboard');
    Route::get('/gpsdashboard/live-data', [GpsDashboardController::class, 'getLiveData'])->name('gpsdashboard.live');
    Route::get('/gpsdashboard/vehicle/{imei}', [GpsDashboardController::class, 'getVehicleDetails'])->name('gpsdashboard.vehicle.details');
    Route::get('/gpsdashboard/vehicle/{imei}/history', [GpsDashboardController::class, 'getVehicleHistory'])->name('gpsdashboard.vehicle.history');
    Route::get('/gpsdashboard/events/recent', [GpsDashboardController::class, 'getRecentEvents'])->name('gpsdashboard.events.recent');
    Route::get('/gpsdashboard/events/recent', [GpsDashboardController::class, 'getRecentEvents'])->name('gpsdashboard.events.recent');
});
