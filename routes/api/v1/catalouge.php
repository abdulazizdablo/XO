<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\HomeController as AdminHomeController;


Route::group(
    [
        'prefix' => '/dashboard/catalouge',
        'as' => 'dashboard.catalouge.'
    ],
    function () {
        // Route::get('', [AdminAddressController::class, 'index'])->name('index');
        Route::get('feedback', [AdminHomeController::class, 'allFeedback'])->name('allFeedback');

        // Route::get('show', [AdminAddressController::class, 'show'])->name('show');
        // Route::post('store', [AdminAddressController::class, 'store'])->name('store');
        // Route::post('update', [AdminAddressController::class, 'update'])->name('update');
        // Route::post('delete', [AdminAddressController::class, 'destroy'])->name('delete');
        // Route::delete('', [AdminAddressController::class, 'forceDelete'])->name('force.delete');
    }
);


