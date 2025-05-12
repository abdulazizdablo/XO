<?php

use App\Http\Controllers\Dashboard\CityController as AdminCityController;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => '/dashboard/cities',
        'as' => 'dashboard.cities.'
    ],
    function () {
        Route::get('', [AdminCityController::class, 'index']);//si
    }
);
