<?php

use App\Http\Controllers\Dashboard\GroupController as AdminGroupController;
use App\Http\Controllers\Users\v1\GroupOfferController as UserGroupOfferController;
use App\Http\Controllers\Dashboard\GroupOffer as AdminGroupProductController;
use App\Http\Controllers\Users\v1\GroupProductController as UserGroupProductController;
use App\Http\Controllers\Dashboard\GroupDiscountController as AdminGroupDiscountController;
use App\Http\Controllers\Users\v1\GroupDiscountController as UserGroupDiscountController;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => '/dashboard/offers',
    'as' => 'dashboard.offers.',
], function () {

    Route::get('', [AdminGroupController::class, 'index'])->name('index');
    Route::get('show', [AdminGroupController::class, 'show'])->name('show');
    Route::post('store', [AdminGroupController::class, 'store'])->name('store');
    Route::post('update', [AdminGroupController::class, 'update'])->name('update');
    Route::post('delete', [AdminGroupController::class, 'destroy'])->name('delete');
    Route::delete('', [AdminGroupController::class, 'forceDelete'])->name('force.delete');

    Route::get('attach', [AdminGroupController::class, 'attachProduct'])->name('attach');
    Route::get('detach', [AdminGroupController::class, 'detachProduct'])->name('detach');
    // });
});

// User Routes
Route::group([
    'prefix' => '/v1/offers',
    'as' => 'offers.',
], function () {
    // done
    Route::get('', [UserGroupOfferController::class, 'index'])->name('index');
    // done
    Route::get('products', [UserGroupOfferController::class, 'products'])->name('products');
    // not done
    Route::get('show', [UserGroupOfferController::class, 'show'])->name('show');
});
