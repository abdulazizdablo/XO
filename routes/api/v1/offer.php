<?php

use App\Http\Controllers\Dashboard\OfferController as AdminOfferController;
use App\Http\Controllers\Users\v1\GroupOfferController as UserGroupOfferController;
use Illuminate\Support\Facades\Route;


// Route::group(
//     [
//         'prefix' => '/dashboard/offers',
//         'as' => 'dashboard.offers.'
//     ],
//     function () {
//         Route::get('', [AdminOfferController::class, 'index'])->name('index');
//         Route::get('show', [AdminOfferController::class, 'show'])->name('show');
//         Route::post('store', [AdminOfferController::class, 'store'])->name('store');
//         Route::post('update', [AdminOfferController::class, 'update'])->name('update');
//         Route::post('delete', [AdminOfferController::class, 'destroy'])->name('delete');
//         Route::delete('', [AdminOfferController::class, 'forceDelete'])->name('force.delete');
//     }
// );


Route::group(
    [
        'prefix' => '/v1/offers',
        'as' => 'offers.'
    ],
    function () {
        Route::get('', [UserGroupOfferController::class, 'index'])->name('index');
        // Route::get('show', [UserOfferController::class, 'show'])->name('show');
        // Route::post('store', [UserOfferController::class, 'store'])->name('store');
        // Route::post('update', [UserOfferController::class, 'update'])->name('update');
        // Route::post('delete', [UserOfferController::class, 'destroy'])->name('delete');
        // Route::delete('', [UserOfferController::class, 'forceDelete'])->name('force.delete');
    }
);
