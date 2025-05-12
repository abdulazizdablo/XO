<?php

use App\Http\Controllers\Dashboard\SectionController as AdminSectionController;
use App\Http\Controllers\Users\v1\SectionController as UserSectionController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/sections',
        'as' => 'dashboard.sections.',
		'middleware' => 'CheckIfEmployee'
    ],
    function () {
        Route::get('', [AdminSectionController::class, 'index']);//si
        Route::get('categories', [AdminSectionController::class, 'getSectionCategories']);//si
        Route::post('popular/categories', [AdminSectionController::class, 'popularCategories']);//si
        Route::get('categories/sub/products', [AdminSectionController::class, 'subCategoriesProducts']);
    }
);

Route::group(
    [
        'prefix' => '/v1/sections',
        'as' => 'sections.',
    ],
    function () {
        Route::get('', [UserSectionController::class, 'index']);//si
        Route::get('categories', [UserSectionController::class, 'getSectionCategories']);//si
        Route::get('sub/categories', [UserSectionController::class, 'getSectionSubCategories']);//si
        Route::get('info', [UserSectionController::class, 'info']);//si
        Route::get('categories/info', [UserSectionController::class, 'getSectionCategoriesInfo']);//si
        Route::get('subCategories',[UserSectionController::class, 'getSectionCategoriesSubs']);//si
    }
);

