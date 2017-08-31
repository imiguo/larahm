<?php

namespace entimm\LaravelPayeer;

use Illuminate\Http\Request;

/**
 * Class Merchant
 */
class Merchant {

    private $config;

    public function __construct($config = [])
    {
        $this->config = array_merge(config('payeer'), $config);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function validatePayment()
    {
        if (app()->environment() != 'local' &&
            !in_array(env('REMOTE_ADDR'), ['185.71.65.92', '185.71.65.189', '149.202.17.210'])) {
            return false;
        }
        $request = app('request');
        $params = [
            $request->input('m_operation_id'),
            $request->input('m_operation_ps'),
            $request->input('m_operation_date'),
            $request->input('m_operation_pay_date'),
            $request->input('m_shop'),
            $request->input('m_orderid'),
            $request->input('m_amount'),
            $request->input('m_curr'),
            $request->input('m_desc'),
            $request->input('m_status'),
        ];
        if ($request->input('m_params')) {
            $params[] = $request->input('m_params');
        }
        $params[] = $this->config['shop_secret_key'];
        $sign_hash = strtoupper(hash('sha256', implode(':', $params)));
        return $request->input('m_sign') == $sign_hash && $request->input('m_status') == 'success';
    }

    /**
     * Render form
     *
     * @param array  $data
     * @param string $view
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render($amount, $order_id = '', $memo = '', $view = 'payeer')
    {
        $amount = number_format($amount, 2);
        $shop_id = $this->config['shop_id'];
        $shop_secret_key = $this->config['shop_secret_key'];
        $currency = $this->config['currency'];
        $memo = base64_encode($memo ?: $this->config['payment_memo']);
        $hash = [
            $shop_id,
            $order_id,
            $amount,
            $currency,
            $memo,
            $shop_secret_key,
        ];
        $shop_sign = strtoupper(hash('sha256', implode(':', $hash)));

        $viewData = compact('amount', 'shop_id', 'order_id', 'shop_sign', 'currency', 'memo');

        if(view()->exists('payeer::' . $view)) {
            return view('payeer::' . $view, $viewData);
        }

        return view('payeer::payeer-form', $viewData);
    }
}