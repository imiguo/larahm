<?php

namespace entimm\LaravelAsmoney;

use Illuminate\Http\Request;

/**
 * Class Asmoney
 */
class Asmoney {

    private $api;

    const PS = 1136053;

    public function __construct()
    {
        $username = config('asmoney.username');
        $apiName = config('asmoney.api_name');
        $apiPassword = config('asmoney.api_password');
        $this->api = new API($username, $apiName, $apiPassword);
    }

    public function balance()
    {
        $r = $api->GetBalance('USD');
        if ($r['result'] == APIerror::OK) {
            return $r['value'];
        }
        throw new AsmoneyException($r['result']);
    }

    public function transactionInfo($batchNum)
    {
        $r = $api->GetTransaction($batchNum);
        if ($r['result'] == APIerror::OK)
        {
            return $r['value'];
        }
        throw new AsmoneyException($r['result']);
    }

    public function transferBTC($bitcoinAddr, $amount, $memo)
    {
        $r = $api->TransferBTC($bitcoinAddr, $amount, 'USD', $memo);
        if ($r['result'] == APIerror::OK) {
            $batchno = $r['value'];
            return $batchno;
        }
        throw new AsmoneyException($r['result']);
    }

    public function transferLitecoin($litecoinAddr, $amount, $memo)
    {
        $r = $api->TransferLTC($litecoinAddr, $amount, 'USD', $memo);
        if ($r['result'] == APIerror::OK) {
            $batchno = $r['value'];
            return $batchno;
        }
        throw new AsmoneyException($r['result']);
    }

    public function history()
    {
        $r = $api->GetHistory(0); // Skip n records from top
        if ($r['result'] == APIerror::OK)
        {
            return $batchno;
        }
        throw new AsmoneyException($r['result']);
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
            md5(config('asmoney.store_password')),
        ];
        return $request->input('MD5_HASH') == implode('|', $params);
    }

    /**
     * Render form
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
            'user_name' => config('asmoney.user_name'),
            'store_name' => config('asmoney.store_name'),
            'payment_units' => config('asmoney.payment_units'),
            'payment_method' => config('asmoney.payment_method'),
            'memo' => $memo ?: config('asmoney.payment_memo'),
        ];

        if(view()->exists('asmoney::' . $view)) {
            return view('asmoney::' . $view, $viewData);
        }

        return view('asmoney::asmoney-form', $viewData);
    }
}
