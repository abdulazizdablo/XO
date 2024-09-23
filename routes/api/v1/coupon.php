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
        Route::get('all', [AdminCouponController::class, 'index'])->name('all');
        Route::get('names', [AdminCouponController::class, 'names'])->name('names');
        Route::get('show', [AdminCouponController::class, 'show'])->name('show');
        Route::post('store', [AdminCouponController::class, 'store'])->name('store');
        Route::post('update', [AdminCouponController::class, 'update'])->name('update');
        Route::post('delete', [AdminCouponController::class, 'destroy'])->name('delete');
        Route::delete('', [AdminCouponController::class, 'forceDelete'])->name('force.delete');
        Route::get('cards', [AdminCouponController::class, 'cards'])->name('cards');
        Route::get('revealGiftCardPassword', [AdminCouponController::class, 'revealGiftCardPassword'])->name('revealGiftCardPassword');


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
        Route::post('checkGiftCard', [UserCouponController::class, 'checkGiftCard'])->name('checkGiftCard');
        Route::post('storeGiftCard', [UserCouponController::class, 'storeGiftCard'])->name('storeGiftCard');
        Route::post('changePassword', [UserCouponController::class, 'changePassword'])->name('changePassword');
        Route::post('update', [UserCouponController::class, 'update'])->name('update');
        Route::post('delete', [UserCouponController::class, 'destroy'])->name('delete');
        Route::get('activeGiftCard', [UserCouponController::class, 'activeGiftCard'])->name('activeGiftCard');
        Route::get('deactiveGiftCard', [UserCouponController::class, 'deactiveGiftCard'])->name('deactiveGiftCard');
        Route::get('revealGiftCardPassword', [UserCouponController::class, 'revealGiftCardPassword'])->name('revealGiftCardPassword');
        Route::post('recharge-gift-card', [UserCouponController::class, 'rechargeGiftCard']);
        Route::get('user-gift-cards', [UserCouponController::class, 'getUserGiftCards']);

        

        

    }
);
