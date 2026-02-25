<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {

    Route::get('login', [LoginController::class, 'create'])->name('login');
    // Login submit - POST
    Route::post('login', [LoginController::class, 'store']);

    Route::get('otp', [OtpController::class, 'show'])
        ->name('otp.form');

    Route::post('otp/verify', [OtpController::class, 'verify'])
        ->name('otp.verify');

    Route::post('otp/resend', [OtpController::class, 'resend'])
        ->name('otp.send');
});
Route::post('logout', [LoginController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');
