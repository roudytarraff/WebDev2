<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\DoctorController;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});






Route::middleware(['isconnected','isAdmin'])->group(function(){

    


});

Route::middleware(['isconnected'])->group(function(){

    
});


Route::get('register',[AuthController::class,'register'])->name('auth.register');
Route::post('doregister',[AuthController::class,'create'])->name('auth.doregister');

Route::get('login',[AuthController::class,'login'])->name('auth.login');
Route::post('dologin',[AuthController::class,'connect'])->name('auth.dologin');

Route::get('logout',[AuthController::class,'logout'])->name('auth.logout');
