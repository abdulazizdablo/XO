<?php

use App\Http\Controllers\Dashboard\VariationController;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => '/dashboard/variations',
    'as' => 'dashboard.variations.',
	'middleware' => 'CheckIfEmployee'
], function () {
    Route::get('', [VariationController::class, 'index'])->name('index');
    Route::get('show', [VariationController::class, 'show'])->name('show');
    Route::post('store', [VariationController::class, 'store'])->name('store');
    Route::post('update', [VariationController::class, 'update'])->name('update');
    Route::post('delete', [VariationController::class, 'destroy'])->name('delete');
    Route::delete('', [VariationController::class, 'forceDelete'])->name('force.delete');
});
