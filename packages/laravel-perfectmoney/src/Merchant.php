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
        $this->config = array_merge(config('perfectmoney'), $config);
    }

    public function validatePayment(Request $request)
    {
        $string = '';
        $string .= $request->input('PAYMENT_ID').':';
        $string .= $request->input('PAYEE_ACCOUNT').':';
        $string .= $request->input('PAYMENT_AMOUNT').':';
        $string .= $request->input('PAYMENT_UNITS').':';
        $string .= $request->input('PAYMENT_BATCH_NUM').':';
        $string .= $request->input('PAYER_ACCOUNT').':';
        $string .= strtoupper(md5($this->config['alt_passphrase'])).':';
        $string .= $request->input('TIMESTAMPGMT');

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
    public function render($payment_amount, $payment_id = '', $view = 'perfectmoney')
    {
        $viewData = [
            'payee_account' => $this->config['marchant_id'],
            'payee_name' => $this->config['marchant_name'],
            'payment_units' => $this->config['units'],
            'payment_url' => $this->config['payment_url'],
            'nopayment_url' => $this->config['nopayment_url'],
            'status_url' => $this->config['status_url'],
            'payment_url_method' => $this->config['payment_url_method'],
            'nopayment_url_method' => $this->config['nopayment_url_method'],
            'memo' => $this->config['suggested_memo'],
        ];
        $viewData = array_merge($viewData, $data);
        $viewData['payment_amount'] = $payment_amount;
        $viewData['payment_id'] = $payment_id;

        // Custom view
        if (view()->exists('perfectmoney::'.$view)) {
            return view('perfectmoney::'.$view, $viewData);
        }

        // Default view
        return view('perfectmoney::perfectmoney-form', $viewData);
    }
}
