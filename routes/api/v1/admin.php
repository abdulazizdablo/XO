<?php

use App\Http\Controllers\Dashboard\AdminController;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => '/dashboard/admins/',
        'as' => 'dashboard.admins.',
    ],
    function () {
        // Accounts
        Route::get('get-accounts', [AdminController::class, 'getAccounts']);
        Route::post('create-account', [AdminController::class, 'createAccount']);
        Route::delete('delete-account', [AdminController::class, 'deleteAccount']);
        Route::put('update-account', [AdminController::class, 'updateAccount']);
        Route::get('assign-role', [AdminController::class, 'assignAccToRole']);
        Route::post('acc-to-emp', [AdminController::class, 'assignAcctoEmp']);
        Route::post('unassign-account', [AdminController::class, 'unassignAcc']);
        Route::get('account-history', [AdminController::class, 'accountHistory']);
        Route::get('reveal-password', [AdminController::class, 'revealPassword']);
        Route::get('current-employee', [AdminController::class, 'getCurrentEmp']);
        Route::get('last-employees', [AdminController::class, 'getLastEmps']);
        Route::post('create-admin-user',[AdminController::class, 'createAdminUser']);
        Route::delete('delete-account',[AdminController::class, 'deleteAccount']);
        Route::delete('force-delete-account',[AdminController::class, 'forceDeleteAccount']);





        
        //Employees
        Route::get('reveal-password-emp', [AdminController::class, 'revealPasswordEmp']);
        Route::post('create-employee', [AdminController::class, 'createEmp']);
        Route::put('update-employee', [AdminController::class, 'updateEmp']);
        Route::delete('delete-employee', [AdminController::class, 'updateAccount']);
        Route::get('get-emps', [AdminController::class, 'displayEmps']);
        Route::get('delivery-admin-details', [AdminController::class, 'deliveryAdminDetails']);
        Route::get('unlinked-employees', [AdminController::class, 'showUnLinkedEmps']);
        Route::get('reveal-password-emp', [AdminController::class, 'revealPasswordEmp']);
        Route::get('delivery-admin', [AdminController::class, 'deliveryAdmin']);
        Route::get('reveal-password-emp', [AdminController::class, 'revealPasswordEmp']);
        Route::get('roles', [AdminController::class, 'getRoles']);
        Route::post('image-save', [AdminController::class, 'imageSave']);
        Route::post('handle', [AdminController::class, 'handle']);





    }
);
