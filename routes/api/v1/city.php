<?php

use App\Http\Controllers\Dashboard\CityController as AdminCityController;
use App\Http\Controllers\Users\v1\CityController as UserCityController;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => '/dashboard/cities',
        'as' => 'dashboard.cities.'
    ],
    function () {
        Route::get('', [AdminCityController::class, 'index'])->name('index');
        Route::get('show', [AdminCityController::class, 'show'])->name('show');
        Route::post('store', [AdminCityController::class, 'store'])->name('store');
        Route::post('update', [AdminCityController::class, 'update'])->name('update');
        Route::post('delete', [AdminCityController::class, 'destroy'])->name('delete');
        Route::delete('', [AdminCityController::class, 'forceDelete'])->name('force.delete');

    }
);

Route::group(
    [
        'prefix' => '/v1/cities',
        'as' => 'cities.'
    ],
    function () {
        Route::get('', [UserCityController::class, 'index'])->name('index');

    }
);
