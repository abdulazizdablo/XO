<?php

use App\Http\Controllers\Dashboard\UserController as AdminUserController;
use App\Http\Controllers\Users\v1\UserController;
use Illuminate\Support\Facades\Route;


Route::group([
	'prefix' => '/dashboard/users',
	'as' => 'dashboard.users.',
	'middleware' => 'CheckIfEmployee'
], function () {
	Route::get('', [AdminUserController::class, 'index']);//si
	Route::get('orders', [AdminUserController::class, 'showUserOrders']);//si
	Route::get('reviews', [AdminUserController::class, 'showUserReviews']);//si
	Route::get('complaints', [AdminUserController::class, 'showUserComplaints']);//si
	Route::get('cards', [AdminUserController::class, 'showUsersCards']);//si
	Route::get('counts', [AdminUserController::class, 'UserCounts']);//si
	Route::post('ban', [AdminUserController::class, 'Ban_user']);//si
	Route::get('unban', [AdminUserController::class, 'UnBan_user']);//si
	Route::get('ban_histroy', [AdminUserController::class, 'ban_histroy']);//si
	Route::delete('deleteUser', [AdminUserController::class, 'deleteUser']);//si
	Route::get('export', [AdminUserController::class, 'export']);//si
});

Route::group([
	'prefix' => '/v1/users',
	'as' => 'users.',
	'middleware' => 'auth'
	
], function () {
	Route::get('order', [UserController::class, 'showOrder']);//si
	Route::get('user', [UserController::class, 'getUserDataByToken']);//si
	Route::get('getUserDataById', [UserController::class, 'getUserDataById']);//si
	Route::put('updatepassword', [UserController::class, 'updatepassword']);//si
	Route::put('updateemail', [UserController::class, 'updateEmail']);//si
	Route::put('updatename', [UserController::class, 'updateName']);//si
	Route::put('update-user-lang', [UserController::class, 'updateUserLang']);//si
	Route::put('updatephone', [UserController::class, 'updatePhone']);//si
	Route::get('get-user-notifications', [UserController::class, 'getUserNotifications']);//si
	Route::delete('delete-notification', [UserController::class, 'deleteUserNotification']);//si

	Route::post('verify-update-phone', [UserController::class, 'verifyUpdatePhone']);//si
	Route::post('/fcm-token', [UserController::class, 'addFcmToken']);//si
	Route::delete('force-delete', [UserController::class, 'forceDelete']);//si
	Route::post('deactivate', [UserController::class, 'deactivate']);//si

	Route::get('createToken', [UserController::class, 'create_user_token']);// for development

});
