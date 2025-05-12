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

		Route::get('index', [SettingController::class, 'getSetting']);//si
		Route::get('all-settings', [SettingController::class, 'getAllSetting']);//si

		Route::post('types-of-problems', [SettingController::class, 'typesOfProblems']);

		Route::post('categories', [SettingController::class, 'updateLoginNotification']);
		Route::post('about-us', [SettingController::class, 'aboutUs']);
		Route::post('login-notifactions', [SettingController::class, 'loginNotifactions']);
		Route::post('ban-user-notifactions', [SettingController::class, 'banUserNotifactions']);
		Route::post('links', [SettingController::class, 'links']);
		Route::post('fees', [SettingController::class, 'fees']);
		Route::post('return-policy', [SettingController::class, 'returnPolicy']);
		Route::post('advertisment-tape', [SettingController::class, 'advertismentTape']);
		Route::post('privacy-policy', [SettingController::class, 'privacyPolicy']);
		Route::post('photos', [SettingController::class, 'photos']);
		Route::post('frequent-questions', [SettingController::class, 'frequentQuestions']);
		Route::post('terms', [SettingController::class, 'terms']);
		Route::post('terms_en', [SettingController::class, 'terms_en']);
		Route::post('navBar-photos', [SettingController::class, 'navBarPhotos']);
		Route::post('app-sections', [SettingController::class, 'app_sections']);
		Route::get('get-app-sections',[SettingController::class,'getAppSections']);
		Route::get('type-of-problems',[SettingController::class,'typeOfProblems']);
		Route::post('addNonReplacableCatgories',[SettingController::class,'addNonReplacableCatgories']);

		Route::post('updateLoginNotification', [SettingController::class, 'updateLoginNotification']);
		Route::get('getSetting', [SettingController::class, 'getSetting']);
		Route::delete('delete-setting', [SettingController::class, 'delete']);
		Route::put('update-notifications', [SettingController::class, 'updateNotifaction']);
		Route::put('update-links', [SettingController::class, 'updateLinks']);

		Route::post('men-photos', [SettingController::class, 'menPhotos']);
		Route::post('women-photos', [SettingController::class, 'womenPhotos']);
		Route::post('kids-photos', [SettingController::class, 'kidsPhotos']);
		Route::post('home-photos', [SettingController::class, 'homePhotos']);
		Route::post('coupon-details', [SettingController::class, 'couponDetails']);
		Route::post('policy-and-security', [SettingController::class, 'policySecurity']);
		Route::post('flash-sale', [SettingController::class, 'flashSale']);
		Route::post('home-page-photos', [SettingController::class, 'homePagePhotos']);
		Route::post('login-photos', [SettingController::class, 'loginPhotos']);
		Route::post('user-complaints', [SettingController::class, 'userComplaints']);
		Route::post('shipping-notes', [SettingController::class, 'shippingNotes']);
		Route::post('store-poligon', [SettingController::class, 'storePoligon']);
		Route::get('get-poligons', [SettingController::class, 'getPoligons']);//si
	}
);
