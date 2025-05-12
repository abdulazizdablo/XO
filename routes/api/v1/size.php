<?php

use App\Http\Controllers\Dashboard\SizeController as AdminSizeController;
use App\Http\Controllers\Users\v1\SizeController as UserSizeController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/sizes',
        'as' => 'dashboard.sizes.'
    ],
    function () {
        Route::get('', [AdminSizeController::class, 'index']);//si
        Route::post('store', [AdminSizeController::class, 'store']);//si
    }
);

Route::group(
    [
        'prefix' => '/v1/sizes',
        'as' => 'sizes.'
    ],
    function () {
        Route::get('', [UserSizeController::class, 'index']);//si
    }
);
