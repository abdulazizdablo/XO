<?php

use App\Http\Controllers\Dashboard\CargoRequestPVController;
use App\Http\Controllers\Dashboard\CargoRequestController;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => '/dashboard/cargo-request/',
        'as' => 'dashboard.cargo-request.',
    ],
    function () {

        Route::post('send-cargo-request', [CargoRequestPVController::class, 'send']);
        Route::delete('delete', [CargoRequestController::class, 'delete']);

        Route::get('inventory-details', [CargoRequestPVController::class, 'getInventoryDetails']);
        Route::get('inventory-stock', [CargoRequestPVController::class, 'getInventoryStock']);
        Route::get('logistics-count', [CargoRequestController::class, 'requestCount']);
        Route::get('import-product', [CargoRequestController::class, 'importProduct']);
		  Route::get('import-product-variation', [CargoRequestController::class, 'importProductByProductVariation']);
        Route::get('search-product', [CargoRequestController::class, 'search']);

        Route::get('get-logistics-requests', [CargoRequestController::class, 'getLogisticsCargoRequests'])->name('all-requests');
        Route::get('get-logistics-myshipments', [CargoRequestController::class, 'getLogisticsMyCargoShipment'])->name('all-shipments');
        Route::get('get-logistics-assignedshipments', [CargoRequestController::class, 'getLogisticsAssignedCargoShipment'])->name('assigned-shipments');


    }
);
