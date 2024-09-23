<?php

use App\Http\Controllers\Dashboard\InventoryController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/inventories',
        'as' => 'dashboard.inventories.'
    ],
    function () {
        Route::get('', [InventoryController::class, 'index'])->name('index');
        Route::get('products_stock', [InventoryController::class, 'getProductsStock']);
        Route::get('InventoryCount', [InventoryController::class, 'getInventoryCount'])->name('getInventoryCount');
        Route::get('search', [InventoryController::class, 'search'])->name('search');
        Route::get('show', [InventoryController::class, 'show'])->name('show');
        Route::post('store', [InventoryController::class, 'store'])->name('store');
        Route::post('update', [InventoryController::class, 'update'])->name('update');
        Route::post('delete', [InventoryController::class, 'destroy'])->name('delete');
        Route::delete('', [InventoryController::class, 'forceDelete'])->name('force.delete');
        Route::post('addToGroup', [InventoryController::class, 'addToGroup'])->name('addToGroup');
        Route::post('assignToSubCategory', [InventoryController::class, 'assignToSubCategory'])->name('assignToSubCategory');
    }
);
