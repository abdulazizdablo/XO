<?php

use App\Http\Controllers\Dashboard\CouponController as AdminCouponController;
use App\Http\Controllers\Users\v1\CouponController as UserCouponController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/coupons',
        'as' => 'dashboard.coupons.',
		'middleware' => 'auth'
		
    ],
    function () {
        Route::get('all', [AdminCouponController::class, 'index']);//si
        Route::get('names', [AdminCouponController::class, 'names']);//si
        Route::get('show', [AdminCouponController::class, 'show']);
        Route::post('store', [AdminCouponController::class, 'store']);//si
        Route::post('update', [AdminCouponController::class, 'update']);
        Route::post('delete', [AdminCouponController::class, 'destroy']);
        Route::delete('', [AdminCouponController::class, 'forceDelete']);
        Route::get('cards', [AdminCouponController::class, 'cards']);//si
        Route::get('revealGiftCardPassword', [AdminCouponController::class, 'revealGiftCardPassword']);


    }
);

Route::group(
    [
        'prefix' => '/v1/coupons',
        'as' => 'coupons.',
		'middleware' => 'auth'
    ],
    function () {
        Route::get('', [UserCouponController::class, 'index']);//si
        Route::get('show', [UserCouponController::class, 'show']);
        Route::get('getCouponByCode', [UserCouponController::class, 'getCouponByCode']);//si
        Route::post('checkGiftCard', [UserCouponController::class, 'checkGiftCard']);//si
        Route::post('checkCoupon', [UserCouponController::class, 'checkGiftCard']);//si
        Route::post('storeGiftCard', [UserCouponController::class, 'storeGiftCard']);//si
        Route::post('changePassword', [UserCouponController::class, 'changePassword']);//si
        Route::post('update', [UserCouponController::class, 'update']);
        Route::post('delete', [UserCouponController::class, 'destroy']);
        Route::get('activeGiftCard', [UserCouponController::class, 'activeGiftCard']);//si
        Route::get('deactiveGiftCard', [UserCouponController::class, 'deactiveGiftCard']);//si
        Route::get('revealGiftCardPassword', [UserCouponController::class, 'revealGiftCardPassword']);
        Route::post('recharge-gift-card', [UserCouponController::class, 'rechargeGiftCard']);//si
        Route::get('user-gift-cards', [UserCouponController::class, 'getUserGiftCards']);//si

        

        

    }
);
