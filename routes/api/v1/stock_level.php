<?php

use App\Http\Controllers\Dashboard\StockLevelController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/stock/level',
        'as' => 'dashboard.stock.level.'
    ],
    function () {
        Route::post('products/stock', [StockLevelController::class, 'getProductsStock'])->name('index');
        Route::get('InventoryCount', [StockLevelController::class, 'getInventoryCount'])->name('getInventoryCount');
        Route::get('', [StockLevelController::class, 'index']);
        Route::get('show', [StockLevelController::class, 'show']);
        Route::post('store', [StockLevelController::class, 'store']);
        Route::post('update', [StockLevelController::class, 'update']);
        Route::post('delete', [StockLevelController::class, 'destroy']);
        Route::delete('', [StockLevelController::class, 'forceDelete']);
    }
);
