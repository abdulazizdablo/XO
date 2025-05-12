<?php

use App\Http\Controllers\Dashboard\GroupController as AdminGroupController;
use App\Http\Controllers\Users\v1\GroupController as UserGroupController;
use App\Http\Controllers\Dashboard\GroupProductController as AdminGroupProductController;
use App\Http\Controllers\Users\v1\GroupProductController as UserGroupProductController;
use App\Http\Controllers\Dashboard\GroupDiscountController as AdminGroupDiscountController;
use App\Http\Controllers\Users\v1\GroupDiscountController as UserGroupDiscountController;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => '/dashboard/groups',
    'as' => 'dashboard.groups.',
], function () {
    Route::get('', [AdminGroupController::class, 'index'])->name('index');
    Route::get('show', [AdminGroupController::class, 'show'])->name('show');
    Route::post('store', [AdminGroupController::class, 'store'])->name('store');
    Route::post('update', [AdminGroupController::class, 'update'])->name('update');
    Route::post('delete', [AdminGroupController::class, 'destroy'])->name('delete');
    Route::delete('', [AdminGroupController::class, 'forceDelete'])->name('force.delete');
});

Route::group([
    'prefix' => '/v1/groups',
    'as' => 'groups.',
], function () {
    Route::get('', [UserGroupController::class, 'index'])->name('index');
    Route::get('show', [UserGroupController::class, 'show'])->name('show');
    Route::get('offer-applied-product', [UserGroupController::class, 'offerAppliedProduct'])->name('show');
});
