<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;

class HmController extends Controller
{
    public function __construct()
    {
        require app_path('Hm').'/lib/config.inc.php';
        $this->middleware('hack.monitors')->only('index');
    }

    public function index()
    {
        $app_file = app_path('Hm').'/http/index.php';

        return hanlder_app($app_file);
    }

    public function callback()
    {
        $app_file = app_path('Hm').'/http/index.php';

        return hanlder_app($app_file);
    }

    public function admin()
    {
        $app_file = app_path('Hm').'/http/admin.php';

        return hanlder_app($app_file);
    }

    public function payment($payment)
    {
        $payments = [
            'payeer',
            'perfectmoney',
            'asmoney',
        ];
        if (in_array($payment, $payments)) {
            $app_file = app_path('Hm').'/http/payments/'.$payment.'.php';

            return hanlder_app($app_file);
        }
        abort(404);
    }
}
