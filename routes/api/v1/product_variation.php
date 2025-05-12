<?php

use App\Http\Controllers\Dashboard\ProductVariationController as AdminProductVariationController;
use App\Http\Controllers\Dashboard\FavouriteController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/product_variations',
        'as' => 'dashboard.product.product_variations.',
    ],
    function () {
        Route::get('', [AdminProductVariationController::class, 'index']);
        Route::get('show', [AdminProductVariationController::class, 'show']);
        Route::get('export', [AdminProductVariationController::class, 'export']);
        Route::post('import', [AdminProductVariationController::class, 'import']);
        Route::get('favourite', [AdminProductVariationController::class, 'getFavourite']);
        Route::get('flash_sales', [AdminProductVariationController::class, 'getFlashSales']);
        Route::post('store', [AdminProductVariationController::class, 'store']);
        Route::put('update', [AdminProductVariationController::class, 'update']);
        Route::post('delete', [AdminProductVariationController::class, 'destroy']);
        Route::delete('', [AdminProductVariationController::class, 'forceDelete']);
    }
);
