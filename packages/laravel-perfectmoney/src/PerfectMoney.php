<?php

namespace entimm\LaravelPerfectMoney;

use Carbon\Carbon;
use GuzzleHttp\Client;

/**
 * Class PerfectMoney.
 */
class PerfectMoney
{
    /**
     * @var string
     */
    protected $account_id;

    /**
     * @var string
     */
    protected $passphrase;

    /**
     * @var string
     */
    protected $alt_passphrase;

    /**
     * @var string
     */
    protected $marchant_id;

    /**
     * @var object
     */
    protected $client;

    /**
     * @var array
     */
    protected $params;

    public function __construct($config = [])
    {
        $config = array_merge(config('perfectmoney', []), $config);

        $this->account_id = $config['account_id'];
        $this->passphrase = $config['passphrase'];
        $this->alt_passphrase = $config['alternate_passphrase'];
        $this->marchant_id = $config['marchant_id'];

        $this->client = new Client([
            'base_uri' => 'https://perfectmoney.is',
            'timeout'  => $config['timeout'],
        ]);

        $this->params = [
            'AccountID' => $this->account_id,
            'PassPhrase' => $this->passphrase,
        ];
    }

    /**
     * Get data from the url.
     *
     * @param string $url
     * @param array  $params
     *
     * @throws PerfectMoneyException
     *
     * @return string
     */
    private function post($url, $params)
    {
        return $content = $this->client->request('POST', $url, [
            'form_params' => $params,
        ])->getBody()->getContents();
    }

    /**
     * get the balance for the wallet.
     *
     * @throws PerfectMoneyException
     *
     * @return array
     */
    public function getBalance()
    {
        // Get data from the server
        $content = $this->post('/acct/balance.asp', $this->params);

        // searching for hidden fields
        if (! preg_match_all("/<input name='(.*)' type='hidden' value='(.*)'>/", $content, $result, PREG_SET_ORDER)) {
            throw new PerfectMoneyException('Invalid output');
        }
        // putting data to array (return error, if have any)
        $data = [];
        foreach ($result as $item) {
            if ($item[1] == 'ERROR') {
                throw new PerfectMoneyException($item[2]);
            }
            $data[$item[1]] = $item[2];
        }

        return $data;
    }

    /**
     * Send Money.
     *
     * @param string $account
     * @param float  $amount
     * @param string $description
     * @param string $payment_id
     *
     * @throws PerfectMoneyException
     *
     * @return array
     */
    public function sendMoney($account, $amount, $description = '', $payment_id = '')
    {
        // Send data from the server
        $params = array_merge($this->params, [
            'Payer_Account' => $this->marchant_id,
            'Payee_Account' => $account,
            'Amount' => $amount,
            'Memo' => $description,
            'PAYMENT_ID' => $payment_id,
        ]);
        $content = $this->post('/acct/confirm.asp', $params);

        // searching for hidden fields
        if (! preg_match_all("/<input name='(.*)' type='hidden' value='(.*)'>/", $content, $result, PREG_SET_ORDER)) {
            throw new PerfectMoneyException('Invalid output');
        }
        // putting data to array (return error, if have any)
        $data = [];
        foreach ($result as $item) {
            if ($item[1] == 'ERROR') {
                throw new PerfectMoneyException($item[2]);
            }
            $data[strtolower($item[1])] = $item[2];
        }

        return $data;
    }

    /**
     * This script demonstrates querying account history
     * using PerfectMoney API interface.
     *
     * @param int   $start_day
     * @param int   $start_month
     * @param null  $start_year
     * @param int   $end_day
     * @param int   $end_month
     * @param int   $end_year
     * @param array $data
     *
     * @throws PerfectMoneyException
     *
     * @return array
     */
    public function getHistory(
        $start_day = null,
        $start_month = null,
        $start_year = null,
        $end_day = null,
        $end_month = null,
        $end_year = null,
        $data = []
    ) {
        $params = array_merge($this->params, [
            'startday' => $start_day ?: Carbon::now()->subYear(1)->day,
            'startmonth' => $start_month ?: Carbon::now()->subYear(1)->month,
            'startyear' => $start_year ?: Carbon::now()->subYear(1)->year,
            'endday' => $end_day ?: Carbon::now()->day,
            'endmonth' => $end_month ?: Carbon::now()->month,
            'endyear' => $end_year ?: Carbon::now()->year,
        ], array_only($data, ['payment_id', 'batchfilter', 'counterfilter', 'metalfilter']));

        if (isset($data['oldsort']) &&
            in_array(strtolower($data['oldsort']),
                ['tstamp', 'batch_num', 'metal_name', 'counteraccount_id', 'amount '])) {
            $params['oldsort'] = $data['oldsort'];
        }
        if (! empty($data['paymentsmade'])) {
            $params['paymentsmade'] = 1;
        }
        if (! empty($data['paymentsreceived'])) {
            $params['paymentsreceived'] = 1;
        }

        // Get data from the server
        $content = $this->post('/acct/historycsv.asp', $params);

        if (substr($content, 0, 63) == 'Time,Type,Batch,Currency,Amount,Fee,Payer Account,Payee Account') {
            $lines = explode("\n", $content);

            // Getting table names (Time,Type,Batch,Currency,Amount,Fee,Payer Account,Payee Account)
            $rows = explode(',', $lines[0]);

            $return_data = [];

            // Fetching history
            $return_data['history'] = [];
            for ($i = 1; $i < count($lines); $i++) {

                // Skip empty lines
                if (empty($lines[$i])) {
                    break;
                }

                // Split line into items
                $items = explode(',', $lines[$i]);

                // Get history items
                $history_line = [];
                foreach ($items as $key => $value) {
                    $history_line[str_replace(' ', '_', strtolower($rows[$key]))] = $value;
                }

                $return_data['history'][] = $history_line;
            }

            return $return_data;
        }

        throw new PerfectMoneyException($content);
    }
}
