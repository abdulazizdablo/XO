<?php

use App\Http\Controllers\Dashboard\ReportController as AdminReportController;
use App\Http\Controllers\Users\v1\ReportController as UserReportController;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => '/dashboard/reports',
    'as' => 'dashboard.reports.'
], function () {
    Route::get('', [AdminReportController::class, 'index'])->name('index');
    Route::get('show', [AdminReportController::class, 'show'])->name('show');
    Route::get('getCards', [AdminReportController::class, 'getCards'])->name('get.cards');
    Route::post('store', [AdminReportController::class, 'store'])->name('store');
    Route::post('reply', [AdminReportController::class, 'replyToReport'])->name('reply');
    // Route::post('update', [AdminReportController::class, 'update'])->name('update');
    // Route::delete('delete', [AdminReportController::class, 'destroy'])->name('delete');
    // Route::delete('', [AdminReportController::class, 'forceDelete'])->name('force.delete');
    // Route::get('reviewCount', [AdminReportController::class, 'reviewCount'])->name('reviewCount');

});

Route::group([
    'prefix' => '/v1/reports',
    'as' => 'reports.'
], function () {
    Route::get('', [UserReportController::class, 'index'])->name('index');
    Route::get('show', [UserReportController::class, 'show'])->name('show');
    Route::get('getCards', [UserReportController::class, 'getCards'])->name('get.cards');
    Route::post('general-report', [UserReportController::class, 'createGeneralReport'])->name('general.report');
    Route::post('order-report', [UserReportController::class, 'createOrderReport'])->name('order.report');
    Route::post('reply', [UserReportController::class, 'replyToReport'])->name('reply');

    // Route::post('update', [UserReportController::class, 'update'])->name('update');
    // Route::post('delete', [UserReportController::class, 'destroy'])->name('delete');
});
