<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\HomeController as AdminHomeController;
use App\Http\Controllers\Dashboard\InventoryController as AdminInventoryController;



Route::group(
    [
        'prefix' => '/dashboard/home',
        'as' => 'dashboard.home.'
    ],
    function () {
        // Route::get('', [AdminAddressController::class, 'index'])->name('index');
        Route::get('feedback', [AdminHomeController::class, 'allFeedback'])->name('allFeedback');
        Route::get('homeCount', [AdminHomeController::class, 'gethomeCount'])->name('home.count');
        Route::get('bestSeller', [AdminHomeController::class, 'bestSeller'])->name('best');
        Route::get('section/orders', [AdminHomeController::class, 'sectionOrders']);
        Route::get('category/orders', [AdminHomeController::class, 'categoryOrders'])->name('category.count');
        Route::get('order/revenues', [AdminHomeController::class, 'revenuesChart'])->name('order.revenue');
        Route::get('order/status', [AdminHomeController::class, 'orderStatusChart'])->name('order.status');
        Route::get('order/counts', [AdminHomeController::class, 'orderCounts'])->name('order.count');
        Route::post('sales/compare', [AdminHomeController::class, 'copmareSales'])->name('sales.compare');
        Route::get('sales/user/compare', [AdminHomeController::class, 'copmareUserSales'])->name('sales.compare.user');
        Route::get('users/visits/chart', [AdminHomeController::class, 'percentageDifference']);

        Route::get('banHistory', [AdminHomeController::class, 'banHistory'])->name('banHistory');
        // Route::get('show', [AdminAddressController::class, 'show'])->name('show');
        // Route::post('store', [AdminAddressController::class, 'store'])->name('store');
        // Route::post('update', [AdminAddressController::class, 'update'])->name('update');
        // Route::post('delete', [AdminAddressController::class, 'destroy'])->name('delete');
        // Route::delete('', [AdminAddressController::class, 'forceDelete'])->name('force.delete');
// Route::post('sendCustomNotification',[DashboardController::class,'sendCustomNotification']);
    // Route::post('sendFcmCustomNotification',[DashboardController::class,'sendFcmCustomNotification']);
        Route::post('sendCustomNotification',[AdminHomeController::class,'sendCustomNotification']);
        Route::post('sendGroupNotification',[AdminHomeController::class,'sendGroupNotification']);
        Route::post('sendCouponNotification',[AdminHomeController::class,'sendCouponNotification']);

        // Route::post('sendLoginNotification',[AdminHomeController::class,'sendLoginNotification']);

    }
);


