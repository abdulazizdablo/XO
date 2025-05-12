<?php

use App\Http\Controllers\Dashboard\ReviewController as AdminReviewController;
use App\Http\Controllers\Users\v1\ReviewController as UserReviewController;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => '/dashboard/reviews',
    'as' => 'dashboard.reviews.'
], function () {
    Route::get('', [AdminReviewController::class, 'index']);//si
    Route::delete('delete', [AdminReviewController::class, 'destroy']);//si
});

Route::group([
    'prefix' => '/v1/reviews',
    'as' => 'reviews.'
], function () {
    Route::get('show', [UserReviewController::class, 'show']);//si //To check with majd because the response have dublicated values
    Route::post('store', [UserReviewController::class, 'store']);//si
    Route::post('update', [UserReviewController::class, 'update']);//si
    Route::post('delete', [UserReviewController::class, 'destroy']);//si
});
