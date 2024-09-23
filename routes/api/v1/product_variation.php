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
        Route::get('', [AdminProductVariationController::class, 'index'])->name('index');
        Route::get('show', [AdminProductVariationController::class, 'show'])->name('show');
        Route::get('export', [AdminProductVariationController::class, 'export'])->name('export');
        Route::post('import', [AdminProductVariationController::class, 'import'])->name('import');
        Route::get('search', [AdminProductVariationController::class, 'searchProduct'])->name('search');
        Route::get('favourite', [AdminProductVariationController::class, 'getFavourite'])->name('favourite');
        Route::get('flash_sales', [AdminProductVariationController::class, 'getFlashSales'])->name('flash_sales');
        Route::post('store', [AdminProductVariationController::class, 'store'])->name('store');
        Route::put('update', [AdminProductVariationController::class, 'update'])->name('update');
        Route::post('delete', [AdminProductVariationController::class, 'destroy'])->name('delete');
        Route::delete('', [AdminProductVariationController::class, 'forceDelete'])->name('force.delete');
    }
);
