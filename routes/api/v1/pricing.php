<?php

use App\Http\Controllers\Dashboard\PricingController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/pricings',
        'as' => 'dashboard.pricings.'
    ],
    function () {
        Route::get('', [PricingController::class, 'index'])->name('index');
        Route::get('show', [PricingController::class, 'show'])->name('show');
        Route::post('store', [PricingController::class, 'store'])->name('store');
        Route::post('update', [PricingController::class, 'update'])->name('update');
        Route::post('bulk/update', [PricingController::class, 'bulkUpdate'])->name('bulk.update');
        Route::post('delete', [PricingController::class, 'destroy'])->name('delete');
        Route::delete('', [PricingController::class, 'forceDelete'])->name('force.delete');
    }
);
