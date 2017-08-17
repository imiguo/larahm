<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::match(['get', 'post'], '/', function () {
    $app_file = app_path('Hm').'/http/index.php';

    return hanlder_app($app_file);
});

Route::match(['get', 'post'], env('ADMIN_ROUTE', '/admin'), function () {
    $app_file = app_path('Hm').'/http/admin.php';

    return hanlder_app($app_file);
});

Route::match(['get', 'post'], '/payments/{payment}', function ($payment) {
    $payments = [
        'payeer',
        'perfectmoney',
    ];
    if (in_array($payment, $payments)) {
        $app_file = app_path('Hm').'/http/payments/'.$payment.'.php';

        return hanlder_app($app_file);
    }
    abort(404);
});
