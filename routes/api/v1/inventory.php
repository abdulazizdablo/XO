<?php

use App\Http\Controllers\Dashboard\InventoryController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/inventories',
        'as' => 'dashboard.inventories.',
		'middleware' => 'CheckIfEmployee'
    ],
    function () {
        Route::get('', [InventoryController::class, 'index']);
        Route::get('products_stock', [InventoryController::class, 'getProductsStock']);
        Route::get('InventoryCount', [InventoryController::class, 'getInventoryCount']);//si
        Route::get('search', [InventoryController::class, 'search']);
        Route::get('show', [InventoryController::class, 'show']);
        Route::post('store', [InventoryController::class, 'store']);//si
        Route::post('update', [InventoryController::class, 'update']);
        Route::post('update-region', [InventoryController::class, 'update_region']);
        Route::post('delete', [InventoryController::class, 'destroy']);
		Route::get('get-regions', [InventoryController::class, 'get_regions'])->withoutMiddleware('CheckIfEmployee');
        Route::delete('', [InventoryController::class, 'forceDelete']);
        Route::post('addToGroup', [InventoryController::class, 'addToGroup']);//si
        Route::post('assignToSubCategory', [InventoryController::class, 'assignToSubCategory']);//si
    }
);
