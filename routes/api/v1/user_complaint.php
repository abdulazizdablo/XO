<?php

use App\Http\Controllers\Dashboard\UserComplaintController as AdminUserComplaintController;
use App\Http\Controllers\Users\v1\UserComplaintController as UserUserComplaintController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/user_complaints',
        'as' => 'dashboard.user_complaints.'
    ],
    function () {
        Route::get('', [AdminUserComplaintController::class, 'index'])->name('index');
        Route::get('show', [AdminUserComplaintController::class, 'show'])->name('show');
        Route::post('store', [AdminUserComplaintController::class, 'store'])->name('store');
        Route::post('update', [AdminUserComplaintController::class, 'update'])->name('update');
        Route::post('delete', [AdminUserComplaintController::class, 'destroy'])->name('delete');
        Route::delete('', [AdminUserComplaintController::class, 'forceDelete'])->name('force.delete');
    }
);

Route::group(
    [
        'prefix' => '/v1/user_complaints',
        'as' => 'user_complaints.'
    ],
    function () {
        Route::get('', [UserUserComplaintController::class, 'index'])->name('index');
        Route::get('show', [UserUserComplaintController::class, 'show'])->name('show');
        Route::post('store', [UserUserComplaintController::class, 'store'])->name('store');
        Route::post('update', [UserUserComplaintController::class, 'update'])->name('update');
        Route::post('delete', [UserUserComplaintController::class, 'destroy'])->name('delete');
    }
);
