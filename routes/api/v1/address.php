<?php

use App\Http\Controllers\Users\v1\AddressController as UserAddressController;
use App\Http\Controllers\Dashboard\GroupController as AdminGroupController;

use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => '/v1/addresses',
        'as' => 'addresses.',
		'middleware' => ['CheckUser']
    ],
    function () {
        Route::get('', [UserAddressController::class, 'index']); //si //done
        Route::get('user-addresses', [UserAddressController::class, 'userAddresses']);//si  //done
        Route::post('store', [UserAddressController::class, 'store']); //si
        Route::post('update', [UserAddressController::class, 'update']);//si
        Route::post('delete', [UserAddressController::class, 'destroy']); //si
        Route::post('destroy', [AdminGroupController::class, 'destroy']); //si
		
    }
);
