<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VehicleMomentController;
use App\Http\Controllers\Api\LoginController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/login', [LoginController::class, 'login']);

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
