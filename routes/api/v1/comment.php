<?php

use App\Http\Controllers\Dashboard\CommentController as AdminCommentController;
use App\Http\Controllers\Dashboard\CommentController as UserCommentController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/comments',
        'as' => 'dashboard.comments.'
    ],
    function () {
        Route::get('', [AdminCommentController::class, 'index'])->name('index');
        Route::get('show', [AdminCommentController::class, 'show'])->name('show');
        Route::post('store', [AdminCommentController::class, 'store'])->name('store');
        Route::post('update', [AdminCommentController::class, 'update'])->name('update');
        Route::post('delete', [AdminCommentController::class, 'destroy'])->name('delete');
        Route::delete('', [AdminCommentController::class, 'forceDelete'])->name('force.delete');
    }
);

// User
Route::group(
    [
        'prefix' => '/v1/comments',
        'as' => 'user.comments'
    ],
    function () {
        Route::get('', [UserCommentController::class, 'index'])->name('index');
        Route::get('show', [UserCommentController::class, 'show'])->name('show');
        Route::post('store', [UserCommentController::class, 'store'])->name('store');
        Route::post('update', [UserCommentController::class, 'update'])->name('update');
        Route::post('delete', [UserCommentController::class, 'destroy'])->name('delete');
    }
);
