<?php

use App\Http\Controllers\Dashboard\CouponController as AdminCouponController;
use App\Http\Controllers\Users\v1\CouponController as UserCouponController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/coupons',
        'as' => 'dashboard.coupons.'
    ],
    function () {
        Route::get('all', [AdminCouponController::class, 'index']);
        Route::get('names', [AdminCouponController::class, 'names']);
        Route::get('show', [AdminCouponController::class, 'show']);
        Route::post('store', [AdminCouponController::class, 'store']);
        Route::post('update', [AdminCouponController::class, 'update']);
        Route::post('delete', [AdminCouponController::class, 'destroy']);
        Route::delete('', [AdminCouponController::class, 'forceDelete']);
        Route::get('cards', [AdminCouponController::class, 'cards']);
        Route::get('revealGiftCardPassword', [AdminCouponController::class, 'revealGiftCardPassword']);


    }
);

Route::group(
    [
        'prefix' => '/v1/coupons',
        'as' => 'coupons.'
    ],
    function () {
        Route::get('', [UserCouponController::class, 'index'])->name('index');
        Route::get('show', [UserCouponController::class, 'show'])->name('show');
        Route::get('getCouponByCode', [UserCouponController::class, 'getCouponByCode']);
        Route::post('checkGiftCard', [UserCouponController::class, 'checkGiftCard']);
        Route::post('checkCoupon', [UserCouponController::class, 'checkGiftCard']);
        Route::post('storeGiftCard', [UserCouponController::class, 'storeGiftCard']);
        Route::post('changePassword', [UserCouponController::class, 'changePassword']);
        Route::post('update', [UserCouponController::class, 'update']);
        Route::post('delete', [UserCouponController::class, 'destroy']);
        Route::get('activeGiftCard', [UserCouponController::class, 'activeGiftCard']);
        Route::get('deactiveGiftCard', [UserCouponController::class, 'deactiveGiftCard']);
        Route::get('revealGiftCardPassword', [UserCouponController::class, 'revealGiftCardPassword']);
        Route::post('recharge-gift-card', [UserCouponController::class, 'rechargeGiftCard']);
        Route::get('user-gift-cards', [UserCouponController::class, 'getUserGiftCards']);

        

        

    }
);
