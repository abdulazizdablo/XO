<?php

use App\Http\Controllers\Dashboard\AddressController as AdminAddressController;
use App\Http\Controllers\Users\v1\AddressController as UserAddressController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/addresses',
        'as' => 'dashboard.addresses.'
    ],
    function () {
        Route::get('', [AdminAddressController::class, 'index'])->name('index');
        Route::get('show', [AdminAddressController::class, 'show'])->name('show');
        Route::post('store', [AdminAddressController::class, 'store'])->name('store');
        Route::post('update', [AdminAddressController::class, 'update'])->name('update');
        Route::post('delete', [AdminAddressController::class, 'destroy'])->name('delete');
        Route::delete('', [AdminAddressController::class, 'forceDelete'])->name('force.delete');
    }
);

Route::group(
    [
        'prefix' => '/v1/addresses',
        'as' => 'addresses.'
    ],
    function () {
        Route::get('', [UserAddressController::class, 'index'])->name('index');
        Route::get('show', [UserAddressController::class, 'show'])->name('show');
		  Route::get('user-addresses', [UserAddressController::class, 'userAddresses'])->name('show');
        Route::post('store', [UserAddressController::class, 'store'])->name('store');
        Route::post('update', [UserAddressController::class, 'update'])->name('update');
        Route::post('delete', [UserAddressController::class, 'destroy'])->name('delete');
		        Route::post('flash', [UserAddressController::class, 'flash'])->name('delete');

    }
);
