<?php

use App\Http\Controllers\Dashboard\OrderController as AdminOrderController;
use App\Http\Controllers\Users\v1\OrderController as UserOrderController;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => '/dashboard/orders',
    'as' => 'dashboard.orders.'
], function () {
    Route::get('', [AdminOrderController::class, 'index'])->name('index');
    Route::get('inventories/chart', [AdminOrderController::class, 'inventoriesChart'])->name('inventories.charts');
    Route::get('counts', [AdminOrderController::class, 'counts'])->name('counts');
    Route::get('show', [AdminOrderController::class, 'show'])->name('show');
    Route::get('order-details', [AdminOrderController::class, 'showOrderDetails']);
    Route::get('show/items', [AdminOrderController::class, 'showItems'])->name('show.items');
    Route::post('store', [AdminOrderController::class, 'store'])->name('store');
    Route::post('update', [AdminOrderController::class, 'update'])->name('update');
    Route::post('delete', [AdminOrderController::class, 'destroy'])->name('delete');
    Route::delete('', [AdminOrderController::class, 'forceDelete'])->name('force.delete');
    Route::get('warehouse-admin-orders',[AdminOrderController::class, 'OrdersWarehouseAdmin']);
    Route::get('open-order-items',[AdminOrderController::class, 'openOrderDetails']);
    Route::get('sub-order-items',[AdminOrderController::class, 'subOrderDetails']);  
    Route::get('get-order-cards',[AdminOrderController::class, 'cards']);
    Route::post('ready-to-deliver',[AdminOrderController::class, 'readyToDeliver']);
    Route::post('send-sub-order',[AdminOrderController::class, 'sendSubOrder']);
    Route::post('confirm-receive-sub',[AdminOrderController::class, 'confirmReceiveSub']);
	Route::post('refund-user',[AdminOrderController::class, 'refundPayment']);
    Route::get('invoice',[AdminOrderController::class, 'getInvoice']);
	Route::get('shipping-details',[AdminOrderController::class, 'shippingInfo']);

});

Route::group([
    'prefix' => '/v1/orders',
    'as' => 'orders.',
    //'middleware' => 'check.sanctum.token'
], function () {

    Route::get('pay', [UserOrderController::class, 'pay'])->name('pay');
    Route::get('', [UserOrderController::class, 'index'])->name('index');
    Route::get('show', [UserOrderController::class, 'show'])->name('show');
    Route::post('store', [UserOrderController::class, 'store'])->name('store');
    Route::post('update', [UserOrderController::class, 'update'])->name('update');
    Route::post('delete', [UserOrderController::class, 'destroy'])->name('delete');
    Route::delete('', [UserOrderController::class, 'forceDelete'])->name('force.delete');
    Route::get('dates',[UserOrderController::class,'dates']);
    Route::post('check-available',[UserOrderController::class,'checkAvailable']);
    Route::post('check-available-in-city',[UserOrderController::class,'checkAvailableInCity']);
    Route::post('order-price',[UserOrderController::class,'getPrice']);
    Route::post('cancel-order',[UserOrderController::class, 'cancelOrder']);


});
