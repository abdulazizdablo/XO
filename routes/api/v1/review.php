<?php

use App\Http\Controllers\Dashboard\ReviewController as AdminReviewController;
use App\Http\Controllers\Users\v1\ReviewController as UserReviewController;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => '/dashboard/reviews',
    'as' => 'dashboard.reviews.'
], function () {
    Route::get('', [AdminReviewController::class, 'index'])->name('index');
    Route::get('show', [AdminReviewController::class, 'show'])->name('show');
    Route::post('store', [AdminReviewController::class, 'store'])->name('store');
    Route::post('update', [AdminReviewController::class, 'update'])->name('update');
    Route::delete('delete', [AdminReviewController::class, 'destroy'])->name('delete');
    Route::delete('', [AdminReviewController::class, 'forceDelete'])->name('force.delete');
    Route::get('reviewCount', [AdminReviewController::class, 'reviewCount'])->name('reviewCount');

});

Route::group([
    'prefix' => '/v1/reviews',
    'as' => 'reviews.'
], function () {
    Route::get('', [UserReviewController::class, 'index'])->name('index');
    Route::get('show', [UserReviewController::class, 'show'])->name('show');
    Route::post('store', [UserReviewController::class, 'store'])->name('store');
    Route::post('update', [UserReviewController::class, 'update'])->name('update');
    Route::post('delete', [UserReviewController::class, 'destroy'])->name('delete');
	Route::get('user-reviews', [UserReviewController::class, 'userReviews'])->name('delete');
});
