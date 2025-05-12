<?php

use App\Http\Controllers\Users\v1\RefundController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => 'v1/refund',
        'as' => 'v1.refund.'
    ],
    function () {
        Route::post('store', [RefundController::class, 'store']);//si
    }
);
