<?php

use App\Http\Controllers\MTNPaymentController;
use App\Http\Controllers\MTNPaymentTestController;
use App\Http\Controllers\SyriateCashController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => 'v1/mtn-cash',
        'as' => 'mtn.cash.',
		//'middleware' => ['auth.sanctum']
    ],
    function () {
        //Route::post('authenticate-merchant', [MTNPaymentController::class, 'authenticateMerchant'])->name('index');
        Route::post('activate-terminal', [MTNPaymentTestController::class, 'activate']);
        Route::post('create-invoice', [MTNPaymentTestController::class, 'createInvoice']);//si
        Route::post('payment-initiate', [MTNPaymentTestController::class, 'initiatePayment']);//si
        Route::post('payment-confirmation', [MTNPaymentTestController::class, 'confirmPayment']);//si
        Route::post('refund-initiate', [MTNPaymentTestController::class, 'initiateRefund']);
        Route::post('refund-confirmation', [MTNPaymentTestController::class, 'confirmRefund']);
        Route::post('refund-cancel', [MTNPaymentTestController::class, 'cancelRefund']);
        Route::post('test', [MTNPaymentTestController::class, 'test']);
    }
);
