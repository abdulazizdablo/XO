<?php

use App\Http\Controllers\SyriatelCashTestController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/v1/syriatel-cash',
        'as' => 'v1.syriatel-cash.',
		//	'middleware' => ['auth:sanctum']
    ],
    function () {
        Route::post('payment-request', [SyriatelCashTestController::class, 'paymentRequest']);//si
        Route::post('payment-confirmation', [SyriatelCashTestController::class, 'paymentConfirmation']);//si
        Route::post('resend-otp', [SyriatelCashTestController::class, 'resendOTP']);//si
        Route::post('test', [SyriatelCashTestController::class, 'test']); //for development

    }
);

