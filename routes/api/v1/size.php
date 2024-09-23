<?php

use App\Http\Controllers\Dashboard\SizeController as AdminSizeController;
use App\Http\Controllers\Users\v1\SizeController as UserSizeController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/sizes',
        'as' => 'dashboard.sizes.'
    ],
    function () {
        Route::get('', [AdminSizeController::class, 'index'])->name('index');
        Route::get('show', [AdminSizeController::class, 'show'])->name('show');
        Route::post('store', [AdminSizeController::class, 'store'])->name('store');
        Route::post('update', [AdminSizeController::class, 'update'])->name('update');
        Route::get('search', [AdminSizeController::class, 'search'])->name('search');

        Route::delete('delete', [AdminSizeController::class, 'destroy'])->name('delete');
        Route::delete('', [AdminSizeController::class, 'forceDelete'])->name('force.delete');
    }
);

Route::group(
    [
        'prefix' => '/v1/sizes',
        'as' => 'sizes.'
    ],
    function () {
        Route::get('', [UserSizeController::class, 'index'])->name('index');
        Route::get('show', [UserSizeController::class, 'show'])->name('show');
        Route::post('store', [UserSizeController::class, 'store'])->name('store');
        Route::post('update', [UserSizeController::class, 'update'])->name('update');
        Route::post('delete', [UserSizeController::class, 'destroy'])->name('delete');
        Route::get('search', [UserSizeController::class, 'search'])->name('search');

    }
);
