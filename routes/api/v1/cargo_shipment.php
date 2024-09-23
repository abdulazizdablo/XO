<?php

use App\Http\Controllers\Dashboard\CargoShipmentController;
use App\Http\Controllers\Dashboard\CargoShipmentPVController;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => '/dashboard/cargo-shipment/',
        'as' => 'dashboard.cargo-shipment.',
    ],
    function () {
   
        Route::get('index', [CargoShipmentController::class, 'index']);
        Route::get('show', [CargoShipmentController::class, 'show']);
        Route::delete('delete', [CargoShipmentController::class, 'delete']);
        Route::post('send-cargo-shipment', [CargoShipmentPVController::class, 'send']);
        Route::get('shipment-details-items', [CargoShipmentController::class, 'shipmentDetailsItems']);
        Route::get('shipment-details', [CargoShipmentController::class, 'requestAndShipmentDetails']);
        Route::get('request-details', [CargoShipmentController::class, 'requestDetails']);
        Route::get('request-details-items', [CargoShipmentController::class, 'requestDetailsItems']);
        Route::post('shipment-arrived', [CargoShipmentPVController::class, 'shiped']);
        Route::get('all-inventories', [CargoShipmentController::class, 'getAllInventories']);
        Route::get('all-shipments', [CargoShipmentController::class, 'getAllShipments']);
        Route::post('confirm-assigned-shipment', [CargoShipmentController::class, 'confirmShipment']);
        Route::post('cargo-shipment-arrived', [CargoShipmentController::class, 'arrived']);








    }
);
