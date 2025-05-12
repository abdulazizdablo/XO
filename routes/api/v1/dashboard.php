<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\HomeController as AdminHomeController;
use App\Http\Controllers\Dashboard\InventoryController as AdminInventoryController;



Route::group(
    [
        'prefix' => '/dashboard/home',
        'as' => 'dashboard.home.',
		'middleware' => 'CheckIfEmployee'
    ],
    function () {
        Route::get('homeCount', [AdminHomeController::class, 'gethomeCount']);//si
        Route::get('bestSeller', [AdminHomeController::class, 'bestSeller']);
        Route::get('section/orders', [AdminHomeController::class, 'sectionOrders']);//si
        Route::get('category/orders', [AdminHomeController::class, 'categoryOrders']);//si
        Route::get('order/revenues', [AdminHomeController::class, 'revenuesChart']);//si
        Route::get('order/status', [AdminHomeController::class, 'orderStatusChart']);//si
        Route::get('order/counts', [AdminHomeController::class, 'orderCounts']);//si
        Route::post('sales/compare', [AdminHomeController::class, 'copmareSales']);//si
        Route::get('sales/user/compare', [AdminHomeController::class, 'copmareUserSales']);//si
        Route::get('users/visits/chart', [AdminHomeController::class, 'percentageDifference']);//si

        Route::get('banHistory', [AdminHomeController::class, 'banHistory']);
        Route::post('sendCustomNotification',[AdminHomeController::class,'sendCustomNotification']);//si
        Route::post('sendGroupNotification',[AdminHomeController::class,'sendGroupNotification']);//si
        Route::post('sendCouponNotification',[AdminHomeController::class,'sendCouponNotification']);//si
    }
);


