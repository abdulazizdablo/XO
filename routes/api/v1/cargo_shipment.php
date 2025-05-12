<?php

use App\Http\Controllers\Dashboard\CargoShipmentController;
use App\Http\Controllers\Dashboard\CargoShipmentPVController;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => '/dashboard/cargo-shipment/',
        'as' => 'dashboard.cargo-shipment.',
		'middleware' => 'CheckIfEmployee'
    ],
    function () {
   
        Route::post('send-cargo-shipment', [CargoShipmentPVController::class, 'send']);//si
        Route::get('shipment-details-items', [CargoShipmentController::class, 'shipmentDetailsItems']);//si
        Route::get('shipment-details', [CargoShipmentController::class, 'requestAndShipmentDetails']);//si
        Route::get('request-details', [CargoShipmentController::class, 'requestDetails']);//si
        Route::get('request-details-items', [CargoShipmentController::class, 'requestDetailsItems']);//si
        Route::post('shipment-arrived', [CargoShipmentPVController::class, 'shiped']);//si
        Route::get('all-shipments', [CargoShipmentController::class, 'getAllShipments']);//si
        Route::post('confirm-assigned-shipment', [CargoShipmentController::class, 'confirmShipment']);//si
    }
);
