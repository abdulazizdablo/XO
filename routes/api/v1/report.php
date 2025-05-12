<?php

use App\Http\Controllers\Dashboard\ReportController as AdminReportController;
use App\Http\Controllers\Users\v1\ReportController as UserReportController;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => '/dashboard/reports',
    'as' => 'dashboard.reports.'
], function () {
    Route::get('', [AdminReportController::class, 'index']);//si
    Route::get('show', [AdminReportController::class, 'show']);//si
    Route::get('getCards', [AdminReportController::class, 'getCards']);//si
    Route::post('store', [AdminReportController::class, 'store']);//si
    Route::post('reply', [AdminReportController::class, 'replyToReport']);//si
});

Route::group([
    'prefix' => '/v1/reports',
    'as' => 'reports.'
], function () {
    Route::get('', [UserReportController::class, 'index']);//si
    Route::get('show', [UserReportController::class, 'show']);//si
    Route::get('getCards', [UserReportController::class, 'getCards']);//si
    Route::post('general-report', [UserReportController::class, 'createGeneralReport']);//si
    Route::post('order-report', [UserReportController::class, 'createOrderReport']);//si
    Route::post('reply', [UserReportController::class, 'replyToReport']);//si
});
