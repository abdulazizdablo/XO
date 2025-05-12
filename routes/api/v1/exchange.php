<?php

use App\Http\Controllers\Users\v1\ExchangeController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/v1/exchange',
        'as' => 'v1.exchange.'
    ],
    function () {
		Route::post('store', [ExchangeController::class, 'store']);//si
		Route::post('test', [ExchangeController::class, 'test']);
		Route::post('check-price-difference', [ExchangeController::class, 'checkPriceDifference']);//si
    }
);
