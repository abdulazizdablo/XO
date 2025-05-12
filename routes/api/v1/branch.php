<?php

use App\Http\Controllers\Users\v1\BranchController as UserBranchController;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => '/v1/branches',
        'as' => 'branches.'
    ],

    function () {
        Route::get('', [UserBranchController::class, 'index']);//si
    }
);
