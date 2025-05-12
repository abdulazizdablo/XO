<?php

use App\Http\Controllers\Dashboard\StockLevelController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/stock/level',
        'as' => 'dashboard.stock.level.',
		'middleware' => 'CheckIfEmployee'
    ],
    function () {
        Route::post('products/stock', [StockLevelController::class, 'getProductsStock']);//si
        Route::post('update', [StockLevelController::class, 'update']);//si
    }
);
