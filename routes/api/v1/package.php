<?php

use App\Http\Controllers\Dashboard\PackageController as AdminPackageController;
use App\Http\Controllers\Users\v1\PackageController as UserPackageController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/packages',
        'as' => 'dashboard.packages.'
    ],
    function () {
        Route::get('', [AdminPackageController::class, 'index'])->name('index');
        Route::get('show', [AdminPackageController::class, 'show'])->name('show');
        Route::post('store', [AdminPackageController::class, 'store'])->name('store');
        Route::post('update', [AdminPackageController::class, 'update'])->name('update');
        Route::post('delete', [AdminPackageController::class, 'destroy'])->name('delete');
        Route::delete('', [AdminPackageController::class, 'forceDelete'])->name('force.delete');
    }
);

Route::group(
    [
        'prefix' => '/v1/packages',
        'as' => 'packages.'
    ],
    function () {
        Route::get('', [UserPackageController::class, 'index'])->name('index');
        Route::get('show', [UserPackageController::class, 'show'])->name('show');
    }
);
