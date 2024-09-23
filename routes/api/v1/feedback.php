<?php

use App\Http\Controllers\Dashboard\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Users\v1\FeedbackController as UserFeedbackController;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => '/dashboard/feedbacks',
    'as' => 'dashboard.feedbacks.'
], function () {
    Route::get('', [AdminFeedbackController::class, 'index'])->name('index');
    Route::get('show', [AdminFeedbackController::class, 'show'])->name('show');
    Route::post('store', [AdminFeedbackController::class, 'store'])->name('store');
    Route::post('update', [AdminFeedbackController::class, 'update'])->name('update');
    Route::post('delete', [AdminFeedbackController::class, 'destroy'])->name('delete');
    Route::delete('', [AdminFeedbackController::class, 'forceDelete'])->name('force.delete');
});

Route::group([
    'prefix' => '/v1/feedbacks',
    'as' => 'feedbacks.'
], function () {
    Route::post('store', [UserFeedbackController::class, 'store'])->name('store');
});
