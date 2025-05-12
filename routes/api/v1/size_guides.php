<?php

// use App\Http\Controllers\Dashboard\SizeController as AdminSizeController;
use App\Http\Controllers\Users\v1\SizeGuideController as SizeGuideController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/v1/size_guides',
        'as' => 'size_guides.'
    ],
    function () {
        Route::get('', [SizeGuideController::class, 'index']);//si
    }
);
