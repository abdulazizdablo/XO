<?php

use App\Http\Controllers\Dashboard\CargoRequestPVController;
use App\Http\Controllers\Dashboard\CargoRequestController;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => '/dashboard/cargo-request/',
        'as' => 'dashboard.cargo-request.',
        'middleware' => 'CheckIfEmployee'
    ],
    function () {
        Route::post('send-cargo-request', [CargoRequestPVController::class, 'send']);//si
        Route::get('logistics-count', [CargoRequestController::class, 'requestCount']);//si
        Route::get('import-product', [CargoRequestController::class, 'importProduct']);//si
        Route::get('get-logistics-requests', [CargoRequestController::class, 'getLogisticsCargoRequests']);//si
        Route::get('get-logistics-myshipments', [CargoRequestController::class, 'getLogisticsMyCargoShipment']);//si
        Route::get('get-logistics-assignedshipments', [CargoRequestController::class, 'getLogisticsAssignedCargoShipment']);//si
    }
);
