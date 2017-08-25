<?php

namespace entimm\LaravelPayeer;

use Illuminate\Http\Request;

/**
 * Class Payeer
 */
class Payeer {

    private $api;

    private $config;

    const PS = 1136053;

    public function __construct($config = [])
    {
        $this->config = array_merge(config('payeer'), $config);

        $this->api = new Api(
            $this->config['account'],
            $this->config['api_id'],
            $this->config['api_secret_key']
        );
    }

    /**
     * Check of balance
     *
     * @return mixed|string
     * @throws PayeerException
     */
    public function balance()
    {
        $balance = $this->api->getBalance();
        return $balance['balance']['USD']['BUDGET'];
    }

    /**
     * Receiving available payment systems
     *
     * @return mixed|string
     * @throws PayeerException
     */
    public function paySystems()
    {
        $balance = $this->api->getPaySystems();
        return $balance;
    }

    /**
     * Payout
     *
     * @return mixed|string
     * @throws PayeerException
     */
    public function payout($amount, $recipient)
    {
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

    /**
     * Information on operation
     *
     * @return mixed|string
     * @throws PayeerException
     */
    public function historyInfo($historyId)
    {
        $history = $this->api->getHistoryInfo($historyId);
        return $history;
    }

    /**
     * Information on operation in shop
     *
     * @return mixed|string
     * @throws PayeerException
     */
    public function shopOrderInfo($shopId, $orderId)
    {
        $shopHistory = $this->api->getShopOrderInfo([
            'shopId' => $shopId,
            'orderId' => $orderId,
        ]);
        return $shopHistory;
    }

    /**
     * Money transfer
     *
     * @return mixed|string
     * @throws PayeerException
     */
    public function transfer($recipient, $amount, $comment)
    {
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

    /**
     * Checking user account
     */
    public function checkUser($userId)
    {
        return $this->api->checkUser(['user' => $userId]);
    }

    /**
     * Conversion rates
     */
    public function exchangeRate()
    {
        $inputExchangeRate = $this->api->getExchangeRate(['output' => 'N']);
        return $inputExchangeRate;
    }

    /**
     * API Merchant
     */
    public function merchant($orderId, $amount, $desc)
    {
            $shop = [
                'm_shop' => $config['shop_id'],
                'm_orderid' => $orderId,
                'm_amount' => number_format($amount, 2, '.', ''),
                'm_curr' => 'USD',
                'm_desc' => base64_encode($desc),
            ];
            $shop['m_sign'] = strtoupper(hash('sha256',
                implode(':', array_merge($shop, [$config['shop_secret_key']]))));

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
}
