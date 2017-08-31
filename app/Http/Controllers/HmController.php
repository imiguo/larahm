<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentReceive;

class HmController extends Controller
{
    public function __construct()
    {
        require app_path('Hm').'/lib/config.php';
        $this->middleware('hack.monitors')->only('index');
    }

    public function index()
    {
        $app_file = app_path('Hm').'/http/index.php';

        return hanlder_app($app_file);
    }

    public function callback(Request $request)
    {
        return redirect()->route('index', $request->query());
    }

    public function admin()
    {
        $app_file = app_path('Hm').'/http/admin.php';

        return hanlder_app($app_file);
    }

    public function payment(Request $request, $payment)
    {
        $payments = [
            'perfectmoney',
            'payeer',
            'asmoney',
        ];

        if (in_array($payment, $payments)) {
            PaymentReceive::create([
                'type' => array_flip($payments)[$payment] + 1,
                'ip' => $request->getClientIp(),
                'data' => $request->all(),
            ]);

            $app_file = app_path('Hm').'/http/payments/'.$payment.'.php';

            return hanlder_app($app_file);
        }
        abort(404);
    }
}
