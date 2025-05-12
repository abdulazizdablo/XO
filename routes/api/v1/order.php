<?php

use App\Http\Controllers\Dashboard\OrderController as AdminOrderController;
use App\Http\Controllers\Users\v1\OrderController as UserOrderController;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => '/dashboard/orders',
    'as' => 'dashboard.orders.'
], function () {
    //Route::get('', [AdminOrderController::class, 'index']);
    Route::get('inventories/chart', [AdminOrderController::class, 'inventoriesChart']);//si
    //Route::get('counts', [AdminOrderController::class, 'counts']);
    //Route::get('show', [AdminOrderController::class, 'show']);
    Route::get('order-details', [AdminOrderController::class, 'showOrderDetails']);//si
	Route::get('export', [AdminOrderController::class, 'export'])->middleware('CheckIfEmployee');//si
    Route::get('show/items', [AdminOrderController::class, 'showItems']);
    Route::post('store', [AdminOrderController::class, 'store']);
    Route::post('update', [AdminOrderController::class, 'update']);
    Route::post('delete', [AdminOrderController::class, 'destroy']);
    Route::delete('', [AdminOrderController::class, 'forceDelete']);
    Route::get('warehouse-admin-orders',[AdminOrderController::class, 'OrdersWarehouseAdmin']);//si
    Route::get('open-order-items',[AdminOrderController::class, 'openOrderDetails']);//si
    Route::get('sub-order-items',[AdminOrderController::class, 'subOrderDetails']); //si 
    Route::get('get-order-cards',[AdminOrderController::class, 'cards']);//si
    Route::post('ready-to-deliver',[AdminOrderController::class, 'readyToDeliver']);//si
    Route::post('send-sub-order',[AdminOrderController::class, 'sendSubOrder']);//si
    Route::post('confirm-receive-sub',[AdminOrderController::class, 'confirmReceiveSub']);//si
	Route::post('refund-user',[AdminOrderController::class, 'refundPayment']);
    Route::get('invoice',[AdminOrderController::class, 'getInvoice']);//si
	Route::get('shipping-details',[AdminOrderController::class, 'shippingInfo']);//si

});

Route::group([
    'prefix' => '/v1/orders',
    'as' => 'orders.',
    'middleware' => 'auth'
], function () {

    Route::get('pay', [UserOrderController::class, 'pay']);
    Route::get('', [UserOrderController::class, 'index']);//si
    Route::get('show', [UserOrderController::class, 'show']);
    Route::post('store', [UserOrderController::class, 'store']);//si
    Route::post('update', [UserOrderController::class, 'update']);
    Route::post('delete', [UserOrderController::class, 'destroy']);
    Route::delete('', [UserOrderController::class, 'forceDelete']);
    Route::get('dates',[UserOrderController::class,'dates']);//si
    Route::post('check-available',[UserOrderController::class,'checkAvailable']);//si
    Route::post('check-available-in-city',[UserOrderController::class,'checkAvailableInCity']);//si
    Route::post('order-price',[UserOrderController::class,'getPrice']);//si
    Route::post('cancel-order',[UserOrderController::class, 'cancelOrder']);//si


});
