<?php

use App\Http\Controllers\Dashboard\EmployeeController as AdminEmployeeController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/employees',
        'as' => 'dashboard.employees.'
    ],
    function () {
        Route::get('', [AdminEmployeeController::class, 'index']);
        Route::get('show', [AdminEmployeeController::class, 'show']);
        Route::post('logout', [AdminEmployeeController::class, 'logout']);//si
        Route::post('store', [AdminEmployeeController::class, 'store']);
        Route::post('update', [AdminEmployeeController::class, 'update']);
        Route::post('delete', [AdminEmployeeController::class, 'destroy']);
        Route::delete('', [AdminEmployeeController::class, 'forceDelete']);
        Route::post('login', [AdminEmployeeController::class, 'loginEmployee']);//si
        Route::post('/fcm-token', [AdminEmployeeController::class, 'addFcmToken']);//si
		Route::put('update-employee-lang', [AdminEmployeeController::class, 'updateEmployeeLang']);

		
        Route::get('employee', [AdminEmployeeController::class, 'getEmployeeDataByToken']);
        Route::get('revealPassword', [AdminEmployeeController::class, 'revealPassword']);
        Route::get('role', [AdminEmployeeController::class, 'getEmployeeRoleByToken']); 
        Route::post('Changerole', [AdminEmployeeController::class, 'Changerole']);
        Route::get('getAllRoles', [AdminEmployeeController::class, 'getAllRoles']);
        Route::get('get-employee-notifications', [AdminEmployeeController::class, 'getUserNotifications']);//si
        Route::post('delete-notifications', [AdminEmployeeController::class, 'deleteNotification']);//si
    }
);
