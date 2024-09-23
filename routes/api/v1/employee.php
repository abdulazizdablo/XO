<?php

use App\Http\Controllers\Dashboard\EmployeeController as AdminEmployeeController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/dashboard/employees',
        'as' => 'dashboard.employees.'
    ],
    function () {
        Route::get('', [AdminEmployeeController::class, 'index'])->name('index');
        Route::get('show', [AdminEmployeeController::class, 'show'])->name('show');
        Route::post('logout', [AdminEmployeeController::class, 'logout'])->name('show');
        Route::post('store', [AdminEmployeeController::class, 'store'])->name('store');
        Route::post('update', [AdminEmployeeController::class, 'update'])->name('update');
        Route::post('delete', [AdminEmployeeController::class, 'destroy'])->name('delete');
        Route::delete('', [AdminEmployeeController::class, 'forceDelete'])->name('force.delete');
        Route::post('login', [AdminEmployeeController::class, 'loginEmployee'])->name('login');
        Route::post('/fcm-token', [AdminEmployeeController::class, 'addFcmToken']);
		Route::put('update-employee-lang', [AdminEmployeeController::class, 'updateEmployeeLang']);

		
        Route::get('employee', [AdminEmployeeController::class, 'getEmployeeDataByToken']);  // Route::get('export', [AdminUserController::class, 'export'])->name('export');
        Route::get('revealPassword', [AdminEmployeeController::class, 'revealPassword']);
        Route::get('role', [AdminEmployeeController::class, 'getEmployeeRoleByToken']);  // Route::get('export', [AdminUserController::class, 'export'])->name('export');
        Route::post('Changerole', [AdminEmployeeController::class, 'Changerole']);  // Route::get('export', [AdminUserController::class, 'export'])->name('export');
        Route::get('getAllRoles', [AdminEmployeeController::class, 'getAllRoles']);  // Route::get('export', [AdminUserController::class, 'export'])->name('export');
        Route::get('get-employee-notifications', [AdminEmployeeController::class, 'getUserNotifications']);  // Route::get('export', [AdminUserController::class, 'export'])->name('export');


    }
);
