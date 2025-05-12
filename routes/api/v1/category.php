<?php

use App\Http\Controllers\Dashboard\CategoryController as AdminCategoryController;
use App\Http\Controllers\Users\v1\CategoryController as UserCategoryController;
use Illuminate\Support\Facades\Route;

Route::group(
	[
		'prefix' => '/dashboard/categories',
		'as' => 'dashboard.categories.',
		'middleware' => 'CheckIfEmployee'
	],
	function () {
		Route::post('store', [AdminCategoryController::class, 'store']);//si
		Route::post('update', [AdminCategoryController::class, 'update']);//si
		Route::delete('delete', [AdminCategoryController::class, 'destroy']);//si
		Route::post('change-visibility', [AdminCategoryController::class, 'changeVisibility']);
		Route::get('counts', [AdminCategoryController::class, 'counts']);//si
		Route::get('sub', [AdminCategoryController::class, 'getSubForCategory']);//
		Route::get('sub/data', [AdminCategoryController::class, 'getSubDataForCategory']);//si  
	}
);

Route::group(
	[
		'prefix' => '/v1/categories',
		'as' => 'categories.'
	],
	function () {
		Route::get('getCategoriesBySlug', [UserCategoryController::class, 'getCategoriesBySlug']);//si
		Route::get('sub_categories', [UserCategoryController::class, 'getSubForCategory']);//si
	}
);
