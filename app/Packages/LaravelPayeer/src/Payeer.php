<?php

namespace entimm\LaravelPayeer;

use Illuminate\Http\Request;

/**
 * Class Payeer
 */
class Payeer {

    private $api;

    const PS = 1136053;

    public function __construct()
    {
        $accountNumber = config('payeer.account');
        $apiId = config('payeer.api_id');
        $apiKey = config('payeer.api_secret_key');
        $this->api = new Api($accountNumber, $apiId, $apiKey);
    }

    /**
     * Authorization
     *
     * @return bool
     * @throws PayeerException
     */
    public function isAuth()
    {
        if ($this->api->isAuth()) {
            return true;
        }
        return false;
    }

    /**
     * Check of balance
     *
     * @return mixed|string
     * @throws PayeerException
     */
    public function balance()
    {
        if ($this->api->isAuth()) {
            $balance = $this->api->getBalance();
            return $balance['balance']['USD']['BUDGET'];
        }
        throw new PayeerException($this->api->getErrors());
    }

    /**
     * Receiving available payment systems
     *
     * @return mixed|string
     * @throws PayeerException
     */
    public function paySystems()
    {
        if ($this->api->isAuth()) {
            $balance = $this->api->getPaySystems();
            return $balance;
        }
        throw new PayeerException($this->api->getErrors());
    }

    /**
     * Payout
     *
     * @return mixed|string
     * @throws PayeerException
     */
    public function payout($amount, $recipient)
    {
        if ($this->api->isAuth()) {
            $initOutput = $this->api->initOutput([
                'ps' => self::PS,
                'sumOut' => $amount, //'sumIn' => $amount,
                'curIn' => 'USD',
                'curOut' => 'USD',
                'param_ACCOUNT_NUMBER' => $recipient,
            ]);
            if ($initOutput && $historyId = $this->api->output()) {
                return $historyId;
            }
            throw new PayeerException($this->api->getErrors());
        }
        throw new PayeerException($this->api->getErrors());
    }

    /**
     * Information on operation
     *
     * @return mixed|string
     * @throws PayeerException
     */
    public function historyInfo($historyId)
    {
        if ($this->api->isAuth()) {
            $history = $this->api->getHistoryInfo($historyId);
            return $history;
        }
        throw new PayeerException($this->api->getErrors());
    }

    /**
     * Information on operation in shop
     *
     * @return mixed|string
     * @throws PayeerException
     */
    public function shopOrderInfo($shopId, $orderId)
    {
        if ($this->api->isAuth()) {
            $shopHistory = $this->api->getShopOrderInfo([
                'shopId' => $shopId,
                'orderId' => $orderId,
            ]);
            return $shopHistory;
        }
        throw new PayeerException($this->api->getErrors());
    }

    /**
     * Money transfer
     *
     * @return mixed|string
     * @throws PayeerException
     */
    public function transfer($recipient, $amount, $comment)
    {
        if ($this->api->isAuth()) {
            $transfer = $this->api->transfer([
                'curIn' => 'USD',
                'sum' => $amount, //'sumOut' => $amount,
                'curOut' => 'USD',
                'to' => $recipient, //mail or id,
                'comment' => $comment,
                //'anonim' => 'Y',
                //'protect' => 'Y',
                //'protectPeriod' => '3',
                //'protectCode' => '12345',
            ]);
            if (empty($transfer['errors'])) {
                return $transfer['historyId'];
            }
            throw new PayeerException($transfer["errors"]);
        }
        throw new PayeerException($this->api->getErrors());
    }

    /**
     * Checking user account
     */
    public function checkUser($userId)
    {
        if ($this->api->isAuth()) {
            return $this->api->checkUser(['user' => $userId]);
        }
        throw new PayeerException($this->api->getErrors());
    }

    /**
     * Conversion rates
     */
    public function exchangeRate()
    {
        if ($this->api->isAuth()) {
            $inputExchangeRate = $this->api->getExchangeRate(['output' => 'N']);
            return $inputExchangeRate;
        }
        throw new PayeerException($this->api->getErrors());
    }

    /**
     * API Merchant
     */
    public function merchant($orderId, $amount, $desc)
    {
        if ($this->api->isAuth()) {
            $shop = [
                'm_shop' => config('payeer.shop_id'),
                'm_orderid' => $orderId,
                'm_amount' => number_format($amount, 2, '.', ''),
                'm_curr' => 'USD',
                'm_desc' => base64_encode($desc),
            ];
            $shop['m_sign'] = strtoupper(hash('sha256',
                implode(':', array_merge($shop, [config('payeer.shop_secret_key')]))));

            $order = $this->api->merchant([
                //'merchantUrl' => 'https://payeer.com/merchant/',
                //'processUrl' => 'https://payeer.com/merchant/',
                'shop' => $shop,
                'lang' => 'en',
                'ps' => [
                    'id' => self::PS,
                    'curr' => 'USD',
                ],
                'form' => [
                    'order_email' => 'support@payeer.com',
                ],
                //'ip' => $_SERVER['REMOTE_ADDR'],
            ]);
            return $order;
        }
        throw new PayeerException($this->api->getErrors());
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public static function validatePayment()
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
        $params[] = config('payeer.shop_secret_key');
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
    public static function render($amount, $order_id = '', $memo = '', $view = 'payeer')
    {
        $amount = number_format($amount, 2);
        $shop_id = config('payeer.shop_id');
        $shop_secret_key = config('payeer.shop_secret_key');
        $currency = config('payeer.currency');
        $memo = $memo ?: config('payeer.payment_memo');
        $memo = base64_encode($memo);
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
