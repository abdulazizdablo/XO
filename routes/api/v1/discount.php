<?php

use App\Http\Controllers\Dashboard\DiscountController as AdminDiscountController;
// use App\Http\Controllers\Users\v1\DiscountController as UserDiscountController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/discounts',
        'as' => 'dashboard.discounts.'
    ],
    function () {
        Route::get('', [AdminDiscountController::class, 'index'])->name('index');
        Route::get('show', [AdminDiscountController::class, 'show'])->name('show');
        Route::post('store', [AdminDiscountController::class, 'store'])->name('store');
        Route::post('update', [AdminDiscountController::class, 'update'])->name('update');
        Route::post('delete', [AdminDiscountController::class, 'destroy'])->name('delete');
        Route::delete('', [AdminDiscountController::class, 'forceDelete'])->name('force.delete');
    }
);
