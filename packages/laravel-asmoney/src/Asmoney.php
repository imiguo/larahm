<?php

namespace entimm\LaravelAsmoney;

/**
 * Class Asmoney.
 */
class Asmoney
{
    private $api;

    const PS = 1136053;

    public function __construct($config = [])
    {
        $this->config = array_merge(config('asmoney', []), $config);

        $this->api = new API(
            $this->config['username'],
            $this->config['api_name'],
            $this->config['api_password']
        );
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
        if ($r['result'] == APIerror::OK) {
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
        if ($r['result'] == APIerror::OK) {
            return $batchno;
        }
        throw new AsmoneyException($r['result']);
    }
}
