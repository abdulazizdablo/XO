<?php

use App\Http\Controllers\Dashboard\StockMovementController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/stock/movements',
        'as' => 'dashboard.stock.movements.'
    ],
    function () {
        Route::post('', [StockMovementController::class, 'index'])->name('index');
        Route::post('send', [StockMovementController::class, 'send'])->name('send');
        Route::get('counts', [StockMovementController::class, 'getCounts'])->name('counts');
        Route::get('show', [StockMovementController::class, 'show'])->name('show');
        Route::get('show/items', [StockMovementController::class, 'showItems'])->name('show/item');
        Route::post('store', [StockMovementController::class, 'store'])->name('store');
        Route::post('update', [StockMovementController::class, 'update'])->name('update');
        Route::post('delete', [StockMovementController::class, 'destroy'])->name('delete');
        Route::delete('', [StockMovementController::class, 'forceDelete'])->name('force.delete');
    }
);
