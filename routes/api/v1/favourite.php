<?php

use App\Http\Controllers\Dashboard\FavouriteController as AdminFavouriteController;
use App\Http\Controllers\Dashboard\FavouriteController as UserFavouriteController;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => '/dashboard/favourites',
    'as' => 'dashboard.favourites.'
], function () {
    Route::get('', [AdminFavouriteController::class, 'index']);
    Route::get('show', [AdminFavouriteController::class, 'show']);
    Route::post('store', [AdminFavouriteController::class, 'store']);
    Route::post('update', [AdminFavouriteController::class, 'update']);
    Route::post('delete', [AdminFavouriteController::class, 'destroy']);
    Route::delete('', [AdminFavouriteController::class, 'forceDelete']);
});

Route::group([
    'prefix' => '/v1/favourites',
    'as' => 'favourites.',
	'middleware' => 'auth'
], function () {
    Route::get('', [UserFavouriteController::class, 'index']);
    Route::get('show', [UserFavouriteController::class, 'show']);
    Route::post('store', [UserFavouriteController::class, 'store']);//si
    Route::post('update', [UserFavouriteController::class, 'update']);
    Route::post('delete', [UserFavouriteController::class, 'destroy']);
    Route::delete('', [UserFavouriteController::class, 'forceDelete']);
});
