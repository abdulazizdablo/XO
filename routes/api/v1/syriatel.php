<?php

use App\Http\Controllers\SyriatelCashController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/v1/syriatel-cash',
        'as' => 'v1.syriatel-cash.',
    ],
    function () {
        Route::post('get-token', [SyriatelCashController::class, 'getToken'])->name('index');
        Route::post('payment-request', [SyriatelCashController::class, 'paymentRequest'])->name('index');
        Route::post('payment-confirmation', [SyriatelCashController::class, 'paymentConfirmation'])->name('index');
        Route::post('resend-otp', [SyriatelCashController::class, 'resendOTP'])->name('index');
        Route::post('test', [SyriatelCashController::class, 'test']);

    }
);

