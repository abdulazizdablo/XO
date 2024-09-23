<?php

use App\Http\Controllers\Users\v1\RegisterUserController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewPasswordController;
use App\Http\Controllers\VerifyEmailController;



Route::group([
    'prefix' => '/v1/user',
    'as' => 'user'
], function () {

    Route::post('register', [RegisterUserController::class, 'register'])->name('register');
    Route::post('login', [RegisterUserController::class, 'login'])->name('login');
    Route::post('verify', [RegisterUserController::class, 'verify']);
    Route::post('resend-code', [RegisterUserController::class, 'resendCode']);
	
    Route::post('verify-otp-password', [RegisterUserController::class, 'verifyForPassword']);

    Route::post('refresh-token', [RegisterUserController::class, 'refreshToken']);


    Route::post('forget-password', [RegisterUserController::class, 'forgotPassword']);
    Route::post('reset-password', [RegisterUserController::class, 'resetPassword']);
    Route::post('logout', [RegisterUserController::class, 'logout']);
    Route::get('current_id', [RegisterUserController::class, 'getTokenId']);
	
    Route::post('revoke-token', [RegisterUserController::class, 'revokeToken']);



});
