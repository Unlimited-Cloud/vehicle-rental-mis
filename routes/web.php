<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/admin-page', function () {
    return "hi";
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
