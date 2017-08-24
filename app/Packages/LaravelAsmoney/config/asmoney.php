<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Your asmoney api name
    |--------------------------------------------------------------------------
    */
    'api_name' => env('AM_API_NAME', ''),

    /*
    |--------------------------------------------------------------------------
    | Your asmoney api password
    |--------------------------------------------------------------------------
    */
    'api_password' => env('AM_API_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Your asmoney username
    |--------------------------------------------------------------------------
    */
    'user_name' => env('AM_USER_NAME', ''),

    /*
    |--------------------------------------------------------------------------
    | Your store name that you
    |--------------------------------------------------------------------------
    */
    'store_name' => env('AM_STORE_NAME', ''),

    /*
    |--------------------------------------------------------------------------
    | The currency you preferred
    |--------------------------------------------------------------------------
    */
    'payment_units' => env('AM_PAYMENT_UNITS', ''),

    /*
    |--------------------------------------------------------------------------
    | Show memo to buyer
    |--------------------------------------------------------------------------
    */
    'payment_memo' => env('AM_PAYMENT_MEMO', ''),

    /*
    |--------------------------------------------------------------------------
    | PPayment Method
    |--------------------------------------------------------------------------
    | Payment Method can be equal to "BITCOIN" or "LITECOIN" , "DOGECOIN" , "DARKCOIN"
    | and "PEERCOIN" and redirect your client to Bitcoin or specified coin payment page,
    | this parameter allow you to accept only one payment method.
    | If Payment Method does not set, your clients can pay you Asmoney, Bitcoin and Litecoin
    */
    'payment_method' => env('AM_PAYMENT_METHOD', ''),

];
