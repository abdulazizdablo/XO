<?php

use App\Http\Controllers\Dashboard\GroupController as AdminGroupController;
use App\Http\Controllers\Users\v1\GroupController as UserGroupController;
use App\Http\Controllers\Dashboard\GroupProductController as AdminGroupProductController;
use App\Http\Controllers\Users\v1\GroupProductController as UserGroupProductController;
use App\Http\Controllers\Dashboard\GroupDiscountController as AdminGroupDiscountController;
use App\Http\Controllers\Users\v1\GroupDiscountController as UserGroupDiscountController;
use Illuminate\Support\Facades\Route;


// Dashboard Routes
Route::group([
    'prefix' => '/dashboard/groups/',
    'as' => 'dashboard.groups.',
], function () {
    Route::get('all', [AdminGroupController::class, 'groups']);
    Route::get('showgroup', [AdminGroupController::class, 'showgroup'])->name('showgroup');
    Route::get('discounts/get', [AdminGroupController::class, 'discounts'])->name('discount');
    Route::get('offers/get', [AdminGroupController::class, 'offers'])->name('offer');
    Route::post('storeOffer', [AdminGroupController::class, 'storeOffer'])->name('storeOffer');
    Route::post('storeDiscount', [AdminGroupController::class, 'storeDiscount'])->name('storeDiscount');
    Route::post('update', [AdminGroupController::class, 'update'])->name('update');
    Route::post('update/valid', [AdminGroupController::class, 'update_valid'])->name('update_valid');
    Route::delete('delete', [AdminGroupController::class, 'destroy']);
    Route::delete('', [AdminGroupController::class, 'forceDelete'])->name('force.delete');


    Route::group([
        'prefix' => 'products',
        'as' => 'products.',
    ], function () {
        // Route::get('', [AdminGroupController::class, 'index'])->name('index');
        Route::get('show', [AdminGroupController::class, 'showDashProducts']);

        Route::get('attach', [AdminGroupController::class, 'attachProduct'])->name('attach');
        Route::get('detach', [AdminGroupController::class, 'detachProduct'])->name('detach');
    });

    Route::group([
        'prefix' => 'discounts',
        'as' => 'discounts.',
    ], function () {
        Route::get('', [AdminGroupController::class, 'index']);

        Route::get('attach', [AdminGroupController::class, 'attach'])->name('attach');
        Route::get('detach', [AdminGroupController::class, 'detach'])->name('detach');
    });

});

// User Routes
Route::group([
    'prefix' => '/v1/groups',
    'as' => 'groups.',
], function () {
    Route::get('', [UserGroupController::class, 'index']);
	Route::get('offers-names', [UserGroupController::class, 'offersNames']);    
    Route::get('discounts', [UserGroupController::class, 'discounts'])->name('discounts');
    Route::get('discounts-f', [UserGroupController::class, 'discountsFlutter']);
    Route::get('offers', [UserGroupController::class, 'offers'])->name('offers');
    Route::get('types', [UserGroupController::class, 'types'])->name('types');

    Route::group([
        'prefix' => '/products',
        'as' => 'products.',
    ], function () {
        Route::get('', [UserGroupController::class, 'groupsProducts'])->name('show');
        Route::get('show', [UserGroupController::class, 'show']);
        Route::get('discounts/get', [UserGroupController::class, 'discounts'])->name('discounts');
        Route::get('offers/get', [UserGroupController::class, 'offers'])->name('offers');
        Route::get('offers/latest', [UserGroupController::class, 'latestOffers']);

    });

    Route::group([
        'prefix' => 'discounts',
        'as' => 'discounts.',
    ], function () {
        Route::get('show', [UserGroupController::class, 'show'])->name('show');
    });
});
