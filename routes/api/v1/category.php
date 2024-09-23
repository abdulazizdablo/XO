<?php

use App\Http\Controllers\Dashboard\CategoryController as AdminCategoryController;
use App\Http\Controllers\Users\v1\CategoryController as UserCategoryController;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => '/dashboard/categories',
        'as' => 'dashboard.categories.'
    ],
    function () {
        Route::get('', [AdminCategoryController::class, 'index'])->name('index');
        Route::get('counts', [AdminCategoryController::class, 'counts'])->name('counts');
        Route::get('show', [AdminCategoryController::class, 'show'])->name('show');
        Route::post('store', [AdminCategoryController::class, 'store']);
        Route::post('update', [AdminCategoryController::class, 'update'])->name('update');
        Route::delete('delete', [AdminCategoryController::class, 'destroy'])->name('delete');
        Route::delete('', [AdminCategoryController::class, 'forceDelete'])->name('force.delete');
        
        Route::post('get-image', [AdminCategoryController::class, 'getImageUrl']);


        Route::get('sub', [AdminCategoryController::class, 'getSubForCategory']);
        Route::get('sub/data', [AdminCategoryController::class, 'getSubDataForCategory']);
    }
);

Route::group(
    [
        'prefix' => '/v1/categories',
        'as' => 'categories.'
    ],
    function () {
        Route::get('', [UserCategoryController::class, 'index'])->name('index');
        // Route::get('show', [UserCategoryController::class, 'show'])->name('index');
	     Route::get('getCategoriesBySlug', [UserCategoryController::class, 'getCategoriesBySlug']);
        Route::get('sub_categories', [UserCategoryController::class, 'getSubForCategory']);
    }
);
