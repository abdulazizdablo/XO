<?php

use App\Http\Controllers\Users\v1\UserLastViewedController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => '/v1/lastViewedProducts',
    'as' => 'lastViewedProducts.'
    ], function(){
        Route::post('/addLastViewedProduct/{id}',[UserLastViewedController::class, 'addLastViewedProduct']);

});

