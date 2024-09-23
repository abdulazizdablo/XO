<?php

return [

    'getToken' => [

        'username' => env('SYRIATEL_USERNAME'),
        'password' => env('SYRIATEL_PASSWORD'),
        'merchant_number' => env('SYRIATEL_MERCHANT_NUMBER'),
        'full_url' => env('SYRIATEL_BASE_URL'). '\\//get-token'


    ],

    'paymentRequest' => [


        'full_url' => env('SYRIATEL_BASE_URL'). '\\//paymentRequest'
    ],


    'paymentConfirmation' => [

        'full_url' => env('SYRIATEL_BASE_URL'). '\\//paymentConfirmation'
    ]

];
