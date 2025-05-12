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
        Route::get('get-order-boys',[AdminDeliveryController::class, 'getOrderBoys']);//si
		Route::get('get-delivery-boy',[AdminDeliveryController::class, 'getDeliveryBoy']); //si       
        Route::get('orders-by-boy', [AdminDeliveryController::class, 'getOrdersByBoy']);//si
        Route::post('assign-order',[AdminDeliveryController::class, 'assignOrder']);//si
        Route::post('start-delivery',[AdminDeliveryController::class, 'startDelivery']);//si
		Route::post('custom-notification',[AdminDeliveryController::class, 'customNotification']);
    });
    
Route::group([
    'prefix' => '/v1/deliveries',
    'as' => 'deliveries.'
], function () {
    Route::get('', [UserDeliveryController::class, 'index']);//si
    Route::get('show', [UserDeliveryController::class, 'show']);//si
    Route::post('confirm-delivered', [UserDeliveryController::class, 'confirmOrderIsDelivered']);//si
    Route::get('personal-profile', [UserDeliveryController::class, 'myAccount']);//si
    Route::get('delivery-history', [UserDeliveryController::class, 'deliveryHistory']);//si
    Route::get('main-page', [UserDeliveryController::class, 'mainPage']);//si
    Route::post('cancel-delivery', [UserDeliveryController::class, 'cancelDelivery']);

});
