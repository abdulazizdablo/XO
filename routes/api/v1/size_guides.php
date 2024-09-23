<?php

// use App\Http\Controllers\Dashboard\SizeController as AdminSizeController;
use App\Http\Controllers\Users\v1\SizeGuideController as SizeGuideController;
use Illuminate\Support\Facades\Route;


// Route::group(
//     [
//         'prefix' => '/dashboard/size_guides',
//         'as' => 'dashboard.size_guides.'
//     ],
//     function () {
//         Route::get('', [AdminSizeController::class, 'index'])->name('index');
//         Route::get('show', [AdminSizeController::class, 'show'])->name('show');
//         Route::post('store', [AdminSizeController::class, 'store'])->name('store');
//         Route::post('update', [AdminSizeController::class, 'update'])->name('update');
//         Route::post('delete', [AdminSizeController::class, 'destroy'])->name('delete');
//         Route::delete('', [AdminSizeController::class, 'forceDelete'])->name('force.delete');
//     }
// );

Route::group(
    [
        'prefix' => '/v1/size_guides',
        'as' => 'size_guides.'
    ],
    function () {
        Route::get('', [SizeGuideController::class, 'index'])->name('index');
        Route::get('show', [SizeGuideController::class, 'show'])->name('show');
        Route::post('store', [SizeGuideController::class, 'store'])->name('store');
        Route::post('update', [SizeGuideController::class, 'update'])->name('update');
        Route::post('delete', [SizeGuideController::class, 'destroy'])->name('delete');
    }
);
