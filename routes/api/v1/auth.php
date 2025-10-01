<?php

use App\Http\Controllers\Users\v1\RegisterUserController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewPasswordController;
use App\Http\Controllers\VerifyEmailController;


Route::middleware(['test-cors'])->group(function () {
    Route::get('/api/test', function () {
        return ['message' => 'This is a test endpoint'];
    });
});




Route::group([
    'prefix' => '/v1/user',
    'as' => 'user',
    //'middleware' => 'test-cors'
], function () {

    Route::post('register', [RegisterUserController::class, 'register']); //si
    Route::post('login', [RegisterUserController::class, 'login']);
    Route::post('verify', [RegisterUserController::class, 'verify']);//si
    Route::post('resend-code', [RegisterUserController::class, 'resendCode']);//si
    Route::post('verify-otp-password', [RegisterUserController::class, 'verifyForPassword']);//si
    Route::post('forget-password', [RegisterUserController::class, 'forgotPassword']);//si
    Route::post('reset-password', [RegisterUserController::class, 'resetPassword']);//->middleware('CheckUser');//si //??red flag
    Route::post('logout', [RegisterUserController::class, 'logout']);//si

    Route::post('refresh-token', [RegisterUserController::class, 'refreshToken']);
    Route::get('current_id', [RegisterUserController::class, 'getTokenId']);
    Route::get('get-user', [RegisterUserController::class, 'getUserByToken']);
    Route::post('revoke-token', [RegisterUserController::class, 'revokeToken']);
});
