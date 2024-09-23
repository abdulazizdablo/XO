<?php

use App\Http\Controllers\Dashboard\UserController as AdminUserController;
use App\Http\Controllers\Users\v1\UserController;
use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => '/dashboard/users',
    'as' => 'dashboard.users.'
], function(){
    Route::get('', [AdminUserController::class, 'index'])->name('index');
    // Route::post('store', [AdminUserController::class, 'store'])->name('store');
    Route::get('show', [AdminUserController::class, 'show'])->name('show');
    Route::get('orders', [AdminUserController::class, 'showUserOrders'])->name('show.orders');
    Route::get('reviews', [AdminUserController::class, 'showUserReviews'])->name('show.reviews');
    Route::get('feedbacks', [AdminUserController::class, 'showUserFeedbacks'])->name('show.feedbacks');
    Route::get('complaints', [AdminUserController::class, 'showUserComplaints'])->name('show.complaints');
    Route::get('cards', [AdminUserController::class, 'showUsersCards'])->name('show.cards');
    Route::get('histories', [AdminUserController::class, 'showUserHistories'])->name('show.histories');
    Route::get('order', [AdminUserController::class, 'showOrder'])->name('order.show');
    Route::get('online', [AdminUserController::class, 'getOnlineUsers']);
    Route::get('percentageDifference', [UserController::class, 'percentageDifference']);
    Route::get('counts', [AdminUserController::class, 'UserCounts'])->name('UserCounts');


    Route::post('ban', [UserController::class, 'Ban_user']);
    Route::get('unban', [UserController::class, 'UnBan_user']);
    Route::get('ban_histroy', [AdminUserController::class, 'ban_histroy']);
    Route::delete('deleteUser', [AdminUserController::class, 'deleteUser'])->name('deleteUser');
	Route::post('magd', [AdminUserController::class, 'majd']);

    // Route::put('update', [AdminUserController::class, 'update']);
    // Route::put('updatepassword', [AdminUserController::class, 'updatepassword']);
    // Route::put('updateemail', [AdminUserController::class, 'updateemail']);
    // Route::put('updatename', [AdminUserController::class, 'updatename']);
    // Route::put('updatephone', [AdminUserController::class, 'updatephone']);
});


Route::group([
    'prefix' => '/v1/users',
    'as' => 'users.'
], function(){
    Route::get('show', [UserController::class, 'show'])->name('show');
    Route::get('orders', [UserController::class, 'showUserOrders'])->name('orders.show');
    Route::get('order', [UserController::class, 'showOrder'])->name('order.show');
    Route::get('user', [UserController::class, 'getUserDataByToken']);  // Route::get('export', [AdminUserController::class, 'export'])->name('export');
    Route::get('getUserDataById', [UserController::class, 'getUserDataById']);  // Route::get('export', [AdminUserController::class, 'export'])->name('export');


    // Route::put('update', [UserController::class, 'update']);
    Route::put('updatepassword', [UserController::class, 'updatepassword']);
    Route::put('updateemail', [UserController::class, 'updateEmail']);
    Route::put('updatename', [UserController::class, 'updateName']);
    Route::put('update-user-lang', [UserController::class, 'updateUserLang']);
    Route::put('updatephone', [UserController::class, 'updatePhone']);
	Route::get('get-user-notifications',[UserController::class, 'getUserNotifications']);
    Route::delete('delete-notification',[UserController::class, 'deleteUserNotification']);

    Route::post('verify-update-phone', [UserController::class, 'verifyUpdatePhone']);
    Route::get('createToken', [UserController::class, 'create_user_token']);
    Route::post('/fcm-token', [UserController::class, 'addFcmToken'])->name('addFcmToken');
    Route::delete('delete', [UserController::class, 'destroy'])->name('delete');
  Route::delete('force-delete', [UserController::class, 'forceDelete'])->name('delete');
	Route::post('deactivate', [UserController::class, 'deactivate'])->name('addFcmToken');

});

// Route::post('/fcm-token', [HomeController::class, 'updateToken'])->name('fcmToken');
// Route::post('/send-notification',[HomeController::class,'notification'])->name('notification');

