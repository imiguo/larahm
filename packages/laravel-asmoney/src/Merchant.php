<?php

namespace entimm\LaravelAsmoney;

use Illuminate\Http\Request;

/**
 * Class Merchant.
 */
class Merchant
{
    private $config;

    public function __construct($config = [])
    {
        $this->config = array_merge(config('asmoney'), $config);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public static function validatePayment()
    {
        $request = app('request');
        $params = [
            $request->input('PAYEE_ACCOUNT'),
            $request->input('PAYER_ACCOUNT'),
            $request->input('PAYMENT_AMOUNT'),
            $request->input('PAYMENT_UNITS'),
            $request->input('BATCH_NUM'),
            $request->input('PAYMENT_ID'),
            $request->input('PAYMENT_STATUS'),
            md5($this->config['store_password']),
        ];

        return $request->input('MD5_HASH') == implode('|', $params);
    }

    /**
     * Render form.
     *
     * @param array  $data
     * @param string $view
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function render($amount, $payment_id = '', $memo = '', $view = 'asmoney')
    {
        $viewData = [
            'amount' => number_format($amount, 2),
            'payment_id' => $payment_id,
            'user_name' => $this->config['user_name'],
            'store_name' => $this->config['store_name'],
            'payment_units' => $this->config['payment_units'],
            'payment_method' => $this->config['payment_method'],
            'memo' => $memo ?: $this->config['payment_memo'],
        ];

        if (view()->exists('asmoney::'.$view)) {
            return view('asmoney::'.$view, $viewData);
        }

        return view('asmoney::asmoney-form', $viewData);
    }
}
