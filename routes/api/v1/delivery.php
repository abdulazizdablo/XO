<?php

use App\Http\Controllers\Dashboard\DeliveryController as AdminDeliveryController;
use App\Http\Controllers\Users\v1\DeliveryController as UserDeliveryController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/deliveries',
        'as' => 'dashboard.deliveries.'
    ],
    function () {
        Route::get('', [AdminDeliveryController::class, 'index']);
        Route::get('get-deliveries', [AdminDeliveryController::class, 'getDeliveries']);
        Route::get('show', [AdminDeliveryController::class, 'show']);
        Route::post('store', [AdminDeliveryController::class, 'store']);
        Route::post('update', [AdminDeliveryController::class, 'update']);
        Route::post('delete', [AdminDeliveryController::class, 'destroy']);
        Route::delete('', [AdminDeliveryController::class, 'forceDelete']);
        Route::get('get-order-boys',[AdminDeliveryController::class, 'getOrderBoys']);
		Route::get('get-delivery-boy',[AdminDeliveryController::class, 'getDeliveryBoy']);        
        Route::get('orders-by-boy', [AdminDeliveryController::class, 'getOrdersByBoy']);
        Route::post('assign-order',[AdminDeliveryController::class, 'assignOrder']);
        Route::post('start-delivery',[AdminDeliveryController::class, 'startDelivery']);
		Route::post('custom-notification',[AdminDeliveryController::class, 'customNotification']);
    });
    
Route::group([
    'prefix' => '/v1/deliveries',
    'as' => 'deliveries.'
], function () {
    Route::get('', [UserDeliveryController::class, 'index']);
    Route::get('show', [UserDeliveryController::class, 'show']);
    Route::post('confirm-delivered', [UserDeliveryController::class, 'confirmOrderIsDelivered']);
    Route::get('personal-profile', [UserDeliveryController::class, 'myAccount']);
    Route::get('delivery-history', [UserDeliveryController::class, 'deliveryHistory']);
    Route::get('main-page', [UserDeliveryController::class, 'mainPage']);
    Route::post('cancel-delivery', [UserDeliveryController::class, 'cancelDelivery']);

});
