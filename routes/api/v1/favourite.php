<?php

use App\Http\Controllers\Dashboard\FavouriteController as AdminFavouriteController;
use App\Http\Controllers\Dashboard\FavouriteController as UserFavouriteController;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => '/dashboard/favourites',
    'as' => 'dashboard.favourites.'
], function () {
    Route::get('', [AdminFavouriteController::class, 'index'])->name('index');
    Route::get('show', [AdminFavouriteController::class, 'show'])->name('show');
    Route::post('store', [AdminFavouriteController::class, 'store'])->name('store');
    Route::post('update', [AdminFavouriteController::class, 'update'])->name('update');
    Route::post('delete', [AdminFavouriteController::class, 'destroy'])->name('delete');
    Route::delete('', [AdminFavouriteController::class, 'forceDelete'])->name('force.delete');
});

Route::group([
    'prefix' => '/v1/favourites',
    'as' => 'favourites.'
], function () {
    Route::get('', [UserFavouriteController::class, 'index'])->name('index');
    Route::get('show', [UserFavouriteController::class, 'show'])->name('show');
    Route::post('store', [UserFavouriteController::class, 'store'])->name('store');
    Route::post('update', [UserFavouriteController::class, 'update'])->name('update');
    Route::post('delete', [UserFavouriteController::class, 'destroy'])->name('delete');
    Route::delete('', [UserFavouriteController::class, 'forceDelete'])->name('force.delete');
});
