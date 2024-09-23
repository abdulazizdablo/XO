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
        Route::get('', [AdminColorController::class, 'index'])->name('index');
        Route::get('show', [AdminColorController::class, 'show'])->name('show');
        Route::post('store', [AdminColorController::class, 'store'])->name('store');
        Route::post('update', [AdminColorController::class, 'update'])->name('update');
        Route::get('search', [AdminColorController::class, 'search'])->name('search');
        Route::delete('delete', [AdminColorController::class, 'destroy'])->name('delete');
        Route::delete('', [AdminColorController::class, 'forceDelete'])->name('force.delete');
    }
);

Route::group(
    [
        'prefix' => '/v1/colors',
        'as' => 'colors.'
    ],
    function () {
        Route::get('', [UserColorController::class, 'index'])->name('index');
        Route::get('show', [UserColorController::class, 'show'])->name('show');
        Route::post('store', [UserColorController::class, 'store'])->name('store');
        Route::post('update', [UserColorController::class, 'update'])->name('update');
        Route::post('delete', [UserColorController::class, 'destroy'])->name('delete');
        Route::get('search', [UserColorController::class, 'search'])->name('search');

    }
);
