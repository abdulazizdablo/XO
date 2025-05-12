<?php

use App\Http\Controllers\Dashboard\PricingController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/pricings',
        'as' => 'dashboard.pricings.'
    ],
    function () {
        Route::get('', [PricingController::class, 'index']);
        Route::get('show', [PricingController::class, 'show']);
        Route::post('store', [PricingController::class, 'store']);
        Route::post('update', [PricingController::class, 'update']);
        Route::post('bulk/update', [PricingController::class, 'bulkUpdate']);//si
        Route::post('delete', [PricingController::class, 'destroy']);
        Route::delete('', [PricingController::class, 'forceDelete']);
    }
);
