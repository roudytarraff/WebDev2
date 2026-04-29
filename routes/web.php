<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;



Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

// REGISTER
Route::get('register', [AuthController::class, 'register'])
    ->name('auth.register');

Route::post('register', [AuthController::class, 'create'])
    ->name('auth.create');


// LOGIN
Route::get('login', [AuthController::class, 'login'])
    ->name('auth.login');

Route::post('login', [AuthController::class, 'connect'])
    ->name('auth.connect');


// LOGOUT
Route::get('logout', [AuthController::class, 'logout'])
    ->name('auth.logout');



/*
|--------------------------------------------------------------------------
| GOOGLE AUTH
|--------------------------------------------------------------------------
*/

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])
    ->name('google.redirect');

Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);


/*
|--------------------------------------------------------------------------
| OTP (2FA)
|--------------------------------------------------------------------------
*/

Route::get('/otp', [AuthController::class, 'otpForm'])
    ->name('otp.form');

Route::post('/otp', [AuthController::class, 'verifyOtp'])
    ->name('otp.verify');



/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (EXAMPLE)
|--------------------------------------------------------------------------
*/

Route::middleware(['isconnected','otp'])->group(function () {

    Route::get('/home', function () {
        return view('home');
    })->name('home');
});


Route::middleware(['isconnected', 'isAdmin'])->group(function () {

    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

});