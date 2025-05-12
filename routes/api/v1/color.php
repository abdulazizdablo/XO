<?php

use App\Http\Controllers\Dashboard\ColorController as AdminColorController;
use App\Http\Controllers\Users\v1\ColorController as UserColorController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/colors',
        'as' => 'dashboard.colors.'
    ],
    function () {
        Route::get('', [AdminColorController::class, 'index']);//si
        Route::post('store', [AdminColorController::class, 'store'])->middleware('CheckIfEmployee');//si
    }
);

Route::group(
    [
        'prefix' => '/v1/colors',
        'as' => 'colors.'
    ],
    function () {
        Route::get('', [UserColorController::class, 'index'])->name('index');
    }
);

