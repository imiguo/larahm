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

use App\Exceptions\HmException;

Route::match(['get', 'post'], '/', function () {
    ob_start();
    try {
        include app_path('Hm').'/http/index.php';
    } catch (HmException $e) {
        $httpReturn = $e->resolveResponse();
    }
    $html = ob_get_clean();
    return $httpReturn ?? $html;
});

Route::match(['get', 'post'], env('ADMIN_ROUTE', '/admin'), function () {
    ob_start();
    try {
        include app_path('Hm').'/http/admin.php';
    } catch (HmException $e) {
        $httpReturn = $e->resolveResponse();
    }
    $html = ob_get_clean();
    return $httpReturn ?? $html;
});

Route::match(['get', 'post'], '/test', function () {
    ob_start();
    try {
        include app_path('Hm').'/http/test.php';
    } catch (HmException $e) {
        $httpReturn = $e->resolveResponse();
    }
    $html = ob_get_clean();
    return $httpReturn ?? $html;
});

Route::match(['get', 'post'], '/payments/[:payment]', function (Request $request) {
    $payments = [
        'payeer',
        'perfectmoney',
    ];
    $payment = $request->inpput('payment');
    if (in_array($payment, $payments)) {
        ob_start();
        try {
            include app_path('Hm').'/http/payments/'.$payment.'.php';
        } catch (HmException $e) {
            $httpReturn = $e->resolveResponse();
        }
        $html = ob_get_clean();
        return $httpReturn ?? $html;
    } else {
        abort(404);
    }
});
