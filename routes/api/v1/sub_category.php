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
        Route::post('store', [AdminSubCategoryController::class, 'store']);//si
        Route::post('assign', [AdminSubCategoryController::class, 'assign']);//si
        Route::post('update', [AdminSubCategoryController::class, 'update']);//si
        Route::post('delete', [AdminSubCategoryController::class, 'destroy']);//si
		Route::post('change-visibility',[AdminSubCategoryController::class,'changeVisibility']);//si

    }
);


Route::group(
    [
        'prefix' => '/v1/sub_categories',
        'as' => 'categories.sub.'
    ],
    function () {
        Route::get('', [UserSubCategoryController::class, 'index']);//si
    }
);
