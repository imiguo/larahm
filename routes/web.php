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
    return include app_path('Hm').'/http/index.php';
});

Route::match(['get', 'post'], env('ADMIN_ROUTE', '/admin'), function () {
    return include app_path('Hm').'/http/admin.php';
});

Route::match(['get', 'post'], '/test', function () {
    return include app_path('Hm').'/http/test.php';
});

Route::match(['get', 'post'], '/payments/[:payment]', function (Request $request) {
    $payments = [
        'payeer',
        'perfectmoney',
    ];
    $payment = $request->inpput('payment');
    if (in_array($payment, $payments)) {
        return include app_path('Hm').'/http/payments/'.$payment.'.php';
    } else {
        abort(404);
    }
});
