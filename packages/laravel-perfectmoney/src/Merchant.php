<?php

namespace entimm\LaravelPerfectMoney;

use Illuminate\Http\Request;

/**
 * Class Merchant.
 */
class Merchant
{
    private $config;

    public function __construct($config = [])
    {
        $this->config = array_merge(config('perfectmoney', []), $config);
    }

    public function validatePayment(Request $request)
    {
        $params = [
            $request->input('PAYMENT_ID'),
            $request->input('PAYEE_ACCOUNT'),
            $request->input('PAYMENT_AMOUNT'),
            $request->input('PAYMENT_UNITS'),
            $request->input('PAYMENT_BATCH_NUM'),
            $request->input('PAYER_ACCOUNT'),
            strtoupper(md5($this->config['alt_passphrase'])),
            $request->input('TIMESTAMPGMT'),
        ];
        $string = implode(':', $params);

        return strtoupper(md5($string)) == $request->input('V2_HASH');
    }

    /**
     * Render form.
     *
     * @param array  $data
     * @param string $view
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render($payment_amount, $payment_id = '', $memo = '', $view = 'perfectmoney')
    {
        $viewData = [
            'payment_id' => $payment_id,
            'payment_amount' => $payment_amount,
            'payee_account' => $this->config['marchant_id'],
            'payee_name' => $this->config['marchant_name'],
            'payment_units' => $this->config['units'],
            'payment_url' => $this->config['payment_url'],
            'nopayment_url' => $this->config['nopayment_url'],
            'status_url' => $this->config['status_url'],
            'payment_url_method' => $this->config['payment_url_method'],
            'nopayment_url_method' => $this->config['nopayment_url_method'],
            'memo' => $memo ?: $this->config['suggested_memo'],
        ];

        // Custom view
        if (view()->exists('perfectmoney::'.$view)) {
            return view('perfectmoney::'.$view, $viewData);
        }

        // Default view
        return view('perfectmoney::perfectmoney-form', $viewData);
    }
}
