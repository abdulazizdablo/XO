<?php

use App\Http\Controllers\Dashboard\AppSettingController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/app_settings',
        'as' => 'dashboard.app_settings.'
    ],
    function () {
        Route::get('index', [AppSettingController::class, 'index']); //si
        Route::post('location-photos', [AppSettingController::class, 'locationPhotos']); //si
        Route::post('offer-photos', [AppSettingController::class, 'offerPhotos']);
        Route::post('section-photos', [AppSettingController::class, 'sectionPhotos']); //si
        Route::post('version-number', [AppSettingController::class, 'versionNumber']); //si
        Route::post('delete', [AppSettingController::class, 'destroy'])->name('delete');
        Route::delete('', [AppSettingController::class, 'forceDelete'])->name('force.delete');
        Route::post('app-sections', [AppSettingController::class, 'app_sections']);
        Route::get('get-app-sections', [AppSettingController::class, 'getAppSections']); //si
        Route::post('gift-card-details', [AppSettingController::class, 'giftCardDetails']); //si
        Route::post('offers', [AppSettingController::class, 'offers']); //si
        Route::post('newIn', [AppSettingController::class, 'newIn']); //si
        Route::post('flashSale', [AppSettingController::class, 'flashSale']); //si
        Route::get('homePagePhotos', [AppSettingController::class, 'homePagePhotos']);
        Route::post('categories-section-photos', [AppSettingController::class, 'categoriesSectionPhotos']); //si
        Route::post('safe-shipping', [AppSettingController::class, 'safeShipping']); //si
        Route::post('free-shipping', [AppSettingController::class, 'freeShipping']); //si
        Route::get('section-categories', [AppSettingController::class, 'sectionCategories']);
        Route::post('measurment', [AppSettingController::class, 'measurment']);
        Route::post('compositionAndCare', [AppSettingController::class, 'compositionAndCare']);
        Route::get('generalDetailsApp', [AppSettingController::class, 'generalDetailsApp']); //si

    }
);
