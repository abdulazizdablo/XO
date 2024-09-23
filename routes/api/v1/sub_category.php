<?php

use App\Http\Controllers\Dashboard\SubCategoryController as AdminSubCategoryController;
use App\Http\Controllers\Users\v1\SubCategoryController as UserSubCategoryController;
use Illuminate\Support\Facades\Route;



Route::group(
    [
        'prefix' => '/dashboard/sub/categories',
        'as' => 'dashboard.categories.sub.'
    ],
    function () {
        Route::get('', [AdminSubCategoryController::class, 'index'])->name('index');
        Route::get('show', [AdminSubCategoryController::class, 'show'])->name('show');
        Route::post('store', [AdminSubCategoryController::class, 'store'])->name('store');
        Route::post('assign', [AdminSubCategoryController::class, 'assign'])->name('assign');
        Route::post('update', [AdminSubCategoryController::class, 'update'])->name('update');
        Route::post('delete', [AdminSubCategoryController::class, 'destroy'])->name('delete');
        Route::delete('', [AdminSubCategoryController::class, 'forceDelete'])->name('force.delete');
        Route::get('products', [AdminSubCategoryController::class, 'getProductForSubCategory'])->name('getProductForSubCategory');

    }
);


Route::group(
    [
        'prefix' => '/v1/sub_categories',
        'as' => 'categories.sub.'
    ],
    function () {
        Route::get('', [UserSubCategoryController::class, 'index'])->name('index');
        Route::get('show', [UserSubCategoryController::class, 'show'])->name('show');
        Route::get('products', [UserSubCategoryController::class, 'getProductForSubCategory'])->name('getProductForSubCategory');
        Route::get('count', [UserSubCategoryController::class, 'getSubCategoriesCount']);
	     Route::get('getSubCategoriesBySlug', [UserSubCategoryController::class, 'getSubCategoriesBySlug']);

		
    }
);
