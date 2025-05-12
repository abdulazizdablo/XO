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
	'middleware' => 'CheckIfEmployee'

], function () {
    Route::get('all', [AdminGroupController::class, 'groups']);//si
    Route::get('showgroup', [AdminGroupController::class, 'showgroup']);//si
    //Route::get('discounts/get', [AdminGroupController::class, 'discounts']);
    //Route::get('offers/get', [AdminGroupController::class, 'offers']);
    Route::post('storeOffer', [AdminGroupController::class, 'storeOffer']);//si
    Route::post('storeDiscount', [AdminGroupController::class, 'storeDiscount']);//si
    Route::post('update', [AdminGroupController::class, 'update']);//si
    Route::post('update/valid', [AdminGroupController::class, 'update_valid']);//si
    Route::delete('delete', [AdminGroupController::class, 'destroy']);//si
    //Route::delete('', [AdminGroupController::class, 'forceDelete']);


    Route::group([
        'prefix' => 'products',
        'as' => 'products.',
    ], function () {
        // Route::get('', [AdminGroupController::class, 'index'])->name('index');
        Route::get('show', [AdminGroupController::class, 'showDashProducts']);//si

        Route::get('attach', [AdminGroupController::class, 'attachProduct']);
        Route::get('detach', [AdminGroupController::class, 'detachProduct']);
    });

    Route::group([
        'prefix' => 'discounts',
        'as' => 'discounts.',
    ], function () {
        Route::get('', [AdminGroupController::class, 'index']);//si

        Route::get('attach', [AdminGroupController::class, 'attach']);
        Route::get('detach', [AdminGroupController::class, 'detach']);
    });

});

// User Routes
Route::group([
    'prefix' => '/v1/groups',
    'as' => 'groups.',
], function () {
    Route::get('', [UserGroupController::class, 'index']);
	Route::get('offers-names', [UserGroupController::class, 'offersNames']);//si  
    Route::get('discounts', [UserGroupController::class, 'discounts']);
    Route::get('discounts-f', [UserGroupController::class, 'discountsFlutter']);//si
    Route::get('offers', [UserGroupController::class, 'offers']);//si
    Route::get('types', [UserGroupController::class, 'types']);//si

    Route::group([
        'prefix' => '/products',
        'as' => 'products.',
    ], function () {
        Route::get('', [UserGroupController::class, 'groupsProducts']);
        Route::get('show', [UserGroupController::class, 'show']);
        Route::get('discounts/get', [UserGroupController::class, 'discounts']);
        Route::get('offers/get', [UserGroupController::class, 'offers']);//si
        Route::get('offers/latest', [UserGroupController::class, 'latestOffers']);

    });

    Route::group([
        'prefix' => 'discounts',
        'as' => 'discounts.',
    ], function () {
        Route::get('show', [UserGroupController::class, 'show']);
    });
});
