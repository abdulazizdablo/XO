<?php

use App\Http\Controllers\Users\v1\ContactController;
use Illuminate\Support\Facades\Route;


Route::group(
    [
        'prefix' => '/v1/contact',
        'as' => 'user.contact.'
    ],
    function () {
        Route::get('', [ContactController::class, 'index'])->name('index');
        Route::get('show', [ContactController::class, 'show'])->name('show');
        Route::post('store', [ContactController::class, 'store'])->name('store');
      
    }
);

// User
