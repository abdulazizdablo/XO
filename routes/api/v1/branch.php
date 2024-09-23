<?php

use App\Http\Controllers\Dashboard\BranchController as AdminBranchController;
use App\Http\Controllers\Users\v1\BranchController as UserBranchController;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => '/dashboard/branches',
        'as' => 'dashboard.branches.'
    ],
    function () {
        Route::get('', [AdminBranchController::class, 'index'])->name('index');
        Route::get('show', [AdminBranchController::class, 'show'])->name('show');
        Route::post('store', [AdminBranchController::class, 'store'])->name('store');
        Route::post('update', [AdminBranchController::class, 'update'])->name('update');
        Route::post('delete', [AdminBranchController::class, 'destroy'])->name('delete');
        Route::delete('', [AdminBranchController::class, 'forceDelete'])->name('force.delete');


    }
);

Route::group(
    [
        'prefix' => '/v1/branches',
        'as' => 'branches.'
    ],
    function () {
        Route::get('', [UserBranchController::class, 'index'])->name('index');

    }
);
