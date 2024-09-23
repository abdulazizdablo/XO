<?php

use App\Http\Controllers\Dashboard\AppSettingController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/app_settings',
        'as' => 'dashboard.app_settings.'
    ],
    function () {
        Route::get('index', [AppSettingController::class, 'index'])->name('index');
        Route::post('location-photos', [AppSettingController::class, 'locationPhotos'])->name('show');
        Route::post('offer-photos', [AppSettingController::class, 'offerPhotos'])->name('store');
        Route::post('section-photos', [AppSettingController::class, 'sectionPhotos'])->name('store');
        Route::post('version-number', [AppSettingController::class, 'versionNumber'])->name('update');
        Route::post('delete', [AppSettingController::class, 'destroy'])->name('delete');
        Route::delete('', [AppSettingController::class, 'forceDelete'])->name('force.delete');
        Route::post('app-sections', [AppSettingController::class, 'app_sections']);
        Route::get('get-app-sections',[AppSettingController::class,'getAppSections']);
		Route::post('gift-card-details',[AppSettingController::class,'giftCardDetails']);
		Route::post('offers',[AppSettingController::class,'offers']);
		Route::post('newIn',[AppSettingController::class,'newIn']);
		Route::post('flashSale',[AppSettingController::class,'flashSale']);
		Route::get('homePagePhotos',[AppSettingController::class,'homePagePhotos']);
		Route::post('categories-section-photos',[AppSettingController::class,'categoriesSectionPhotos']);
				Route::post('safe-shipping',[AppSettingController::class,'safeShipping']);
		Route::post('free-shipping',[AppSettingController::class,'freeShipping']);
		Route::get('section-categories',[AppSettingController::class,'sectionCategories']);
		Route::post('measurment',[AppSettingController::class,'measurment']);
		Route::post('compositionAndCare',[AppSettingController::class,'compositionAndCare']);
	Route::get('generalDetailsApp',[AppSettingController::class,'generalDetailsApp']);

    }
);
