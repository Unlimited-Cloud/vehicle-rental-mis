<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VehicleDetailsController;
use App\Http\Controllers\Admin\CrewProfilesController;
use App\Http\Controllers\Admin\VehicleAssignmentController;
// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', function () {
    return redirect()->route('login');
});

require __DIR__ . '/auth.php';


// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::namespace('App\Http\Controllers\Admin')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});

Route::namespace('App\Http\Controllers\Admin')->middleware(['auth'])->prefix('dashboard')->name('admin.')->group(function () {
    Route::resource('vehicles', VehicleController::class);
    Route::resource('users', UserController::class);
    Route::resource('vehicle_details', VehicleDetailsController::class);
    Route::resource('crew_profiles', CrewProfilesController::class);
    Route::resource('vehicle_assignments', VehicleAssignmentController::class);
});
