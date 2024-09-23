<?php

use App\Http\Controllers\Dashboard\SettingController;
use Illuminate\Support\Facades\Route;





/*Route::group(
    [
        'prefix' => '/v1/settings',
        'as' => 'settings.',
    ],
    function () {
        
       Route::get('index', [SettingController::class, 'getSetting'])->name('updateLoginNotification');

  
    }
);

*/





Route::group(
    [
        'prefix' => '/dashboard/settings',
        'as' => 'dashboard.settings.',
    ],
    function () {
        
       Route::get('index', [SettingController::class, 'getSetting'])->name('updateLoginNotification');
       Route::get('all-settings', [SettingController::class, 'getAllSetting'])->name('updateLoginNotification');

       Route::post('types-of-problems', [SettingController::class, 'typesOfProblems'])->name('updateLoginNotification');

        Route::post('categories', [SettingController::class, 'updateLoginNotification'])->name('updateLoginNotification');
        Route::post('about-us', [SettingController::class, 'aboutUs'])->name('updateLoginNotification');
        Route::post('login-notifactions', [SettingController::class, 'loginNotifactions'])->name('updateLoginNotification');
        Route::post('ban-user-notifactions', [SettingController::class, 'banUserNotifactions'])->name('updateLoginNotification');
        Route::post('links', [SettingController::class, 'links'])->name('updateLoginNotification');
        Route::post('fees', [SettingController::class, 'fees'])->name('updateLoginNotification');
        Route::post('return-policy', [SettingController::class, 'returnPolicy'])->name('updateLoginNotification');
        Route::post('advertisment-tape', [SettingController::class, 'advertismentTape'])->name('updateLoginNotification');
        Route::post('privacy-policy', [SettingController::class, 'privacyPolicy'])->name('updateLoginNotification');
        Route::post('photos', [SettingController::class, 'photos'])->name('updateLoginNotification');
        Route::post('frequent-questions', [SettingController::class, 'frequentQuestions'])->name('updateLoginNotification');
        Route::post('terms', [SettingController::class, 'terms'])->name('updateLoginNotification');
        Route::post('terms_en', [SettingController::class, 'terms_en'])->name('updateLoginNotification');
        Route::post('navBar-photos', [SettingController::class, 'navBarPhotos'])->name('updateLoginNotification');
        Route::post('app-sections', [SettingController::class, 'app_sections']);
        Route::get('get-app-sections',[SettingController::class,'getAppSections']);
        Route::get('type-of-problems',[SettingController::class,'typeOfProblems']);
  Route::post('addNonReplacableCatgories',[SettingController::class,'addNonReplacableCatgories']);

        Route::post('updateLoginNotification', [SettingController::class, 'updateLoginNotification'])->name('updateLoginNotification');
        Route::get('getSetting', [SettingController::class, 'getSetting'])->name('getSetting');
        Route::delete('delete-setting', [SettingController::class, 'delete'])->name('updateLoginNotification');
        Route::put('update-notifications', [SettingController::class, 'updateNotifaction'])->name('updateLoginNotification');
        Route::put('update-links', [SettingController::class, 'updateLinks'])->name('updateLoginNotification');
        
        Route::post('men-photos', [SettingController::class, 'menPhotos'])->name('updateLoginNotification');
        Route::post('women-photos', [SettingController::class, 'womenPhotos'])->name('updateLoginNotification');
        Route::post('kids-photos', [SettingController::class, 'kidsPhotos'])->name('updateLoginNotification');
        Route::post('home-photos', [SettingController::class, 'homePhotos'])->name('updateLoginNotification');
        Route::post('coupon-details', [SettingController::class, 'couponDetails'])->name('updateLoginNotification');
        Route::post('policy-and-security', [SettingController::class, 'policySecurity'])->name('updateLoginNotification');
        Route::post('flash-sale', [SettingController::class, 'flashSale'])->name('updateLoginNotification');
        Route::post('home-page-photos', [SettingController::class, 'homePagePhotos'])->name('updateLoginNotification');
        Route::post('user-complaints', [SettingController::class, 'userComplaints'])->name('updateLoginNotification');
        Route::post('shipping-notes', [SettingController::class, 'shippingNotes'])->name('updateLoginNotification');
        Route::post('store-poligon', [SettingController::class, 'storePoligon']);
        Route::get('get-poligons', [SettingController::class, 'getPoligons']);





        


    }
);
