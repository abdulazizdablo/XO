<?php

use App\Http\Controllers\Dashboard\SectionController as AdminSectionController;
use App\Http\Controllers\Users\v1\SectionController as UserSectionController;
use App\Services\SectionService;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/sections',
        'as' => 'dashboard.sections.',
    ],
    function () {
        Route::get('', [AdminSectionController::class, 'index'])->name('index');
        Route::get('show', [AdminSectionController::class, 'show'])->name('show');
        Route::post('store', [AdminSectionController::class, 'store'])->name('store');
        Route::post('update', [AdminSectionController::class, 'update'])->name('update');
        Route::post('delete', [AdminSectionController::class, 'destroy'])->name('delete');
        Route::delete('', [AdminSectionController::class, 'forceDelete'])->name('force.delete');

        // done
        Route::get('sales', [AdminSectionController::class, 'getSectionsSales']);
        Route::get('categories', [AdminSectionController::class, 'getSectionCategories']);
        //
        Route::get('categories/sub/products', [AdminSectionController::class, 'subCategoriesProducts'])->name('categories.sub');
        Route::get('categories/chart', [AdminSectionController::class, 'getSectionChart'])->name('categories');
        Route::post('popular/categories', [AdminSectionController::class, 'popularCategories'])->name('compare');
    }
);

Route::group(
    [
        'prefix' => '/v1/sections',
        'as' => 'sections.',
    ],
    function () {
        Route::get('', [UserSectionController::class, 'index'])->name('index');
        Route::get('show', [UserSectionController::class, 'show'])->name('show');
        Route::get('categories', [UserSectionController::class, 'getSectionCategories'])->name('categories');
        Route::get('sub/categories', [UserSectionController::class, 'getSectionSubCategories']);
        Route::get('info', [UserSectionController::class, 'info'])->name('info');
        Route::get('categories/info', [UserSectionController::class, 'getSectionCategoriesInfo']);
        Route::get('subCategories',[UserSectionController::class, 'getSectionCategoriesSubs']);

    }
);

