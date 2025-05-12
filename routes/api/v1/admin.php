<?php

use App\Http\Controllers\Dashboard\AdminController;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => '/dashboard/admins/',
        'as' => 'dashboard.admins.',
        'middleware' => [/*'CheckIfEmployee', */'CheckIsSuperAdmin']
    ],
    function () {
        Route::post('create-account', [AdminController::class, 'createAccount']); //si
        Route::delete('delete-account', [AdminController::class, 'deleteAccount']); //si
        Route::put('update-account', [AdminController::class, 'updateAccount']); //si
        Route::post('acc-to-emp', [AdminController::class, 'assignAcctoEmp']); //si
        Route::post('unassign-account', [AdminController::class, 'unassignAcc']); //si
        Route::get('reveal-password', [AdminController::class, 'revealPassword']); //si // not used anymore
        Route::get('current-employee', [AdminController::class, 'getCurrentEmp']); //si
        Route::get('last-employees', [AdminController::class, 'getLastEmps']); //si
        Route::get('reveal-password-emp', [AdminController::class, 'revealPasswordEmp']); //si
        Route::post('create-employee', [AdminController::class, 'createEmp']); //si
        Route::put('update-employee', [AdminController::class, 'updateEmp']); //si
        Route::put('update-employee-pass', [AdminController::class, 'updateEmpPass']); //si
        Route::get('get-accounts', [AdminController::class, 'getAccounts']); //si
        Route::get('get-unassigned-accounts', [AdminController::class, 'getUnassignedAccounts']); //si
        Route::get('get-emps', [AdminController::class, 'displayEmps']); //si
        Route::get('delivery-admin-details', [AdminController::class, 'deliveryAdminDetails']); //si //to check with Omar if it is used
        Route::get('unlinked-employees', [AdminController::class, 'showUnLinkedEmps']); //si
        Route::get('delivery-admin', [AdminController::class, 'deliveryAdmin']); //si





    }
);
