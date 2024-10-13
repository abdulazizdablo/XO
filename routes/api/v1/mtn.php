<?php

use App\Http\Controllers\MTNPaymentController;
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
        Route::post('activate-terminal', [MTNPaymentController::class, 'activate']);

        Route::post('create-invoice', [MTNPaymentController::class, 'createInvoice']);
        Route::post('payment-initiate', [MTNPaymentController::class, 'initiatePayment']);
        Route::post('payment-confirmation', [MTNPaymentController::class, 'confirmPayment']);
        Route::post('refund-initiate', [MTNPaymentController::class, 'initiateRefund']);
        Route::post('refund-confirmation', [MTNPaymentController::class, 'confirmRefund']);
        Route::post('refund-cancel', [MTNPaymentController::class, 'cancelRefund']);
        Route::post('test', [MTNPaymentController::class, 'test']);
    }
);
