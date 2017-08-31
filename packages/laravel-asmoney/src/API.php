<?php

namespace entimm\LaravelAsmoney;

class API
{
    public $username;
    public $apiname;
    public $password;
    public $client;

    public function __construct($pusername, $papiname, $ppassword)
    {
        $this->username = $pusername;
        $this->apiname = $papiname;
        $this->password = $ppassword;
        $this->client = new jsonRPCClient('https://www.asmoney.com/api.ashx');
    }

    public function GetBalance($currency)
    {
        $r = $this->client->getbalance($this->username, $this->apiname, $this->password, $currency);
        if (strcmp($r, 'Invalid user') == 0) {
            return ['result' => APIerror::InvalidUser];
        }
        if (strcmp($r, 'Invalid api data') == 0) {
            return ['result' => APIerror::InvalidAPIData];
        }
        if (strcmp($r, 'Invalid IP') == 0) {
            return ['result' => APIerror::InvalidIP];
        }
        if (strcmp($r, 'Invalid IP setup') == 0) {
            return ['result' => APIerror::InvalidIPSetup];
        }
        if (strcmp($r, 'Invalid') == 0) {
            return ['result' => APIerror::InvalidCurrency];
        }

        return ['result' => APIerror::OK, 'value' => $r];
    }

    public function Transfer($touser, $amount, $currency, $memo)
    {
        $r = $this->client->transfer($this->username, $this->apiname, $this->password, $amount, $currency, $touser, $memo);
        if (strcmp($r, 'Invalid user') == 0) {
            return ['result' => APIerror::InvalidUser];
        }
        if (strcmp($r, 'Invalid api data') == 0) {
            return ['result' => APIerror::InvalidAPIData];
        }
        if (strcmp($r, 'Invalid IP') == 0) {
            return ['result' => APIerror::InvalidIP];
        }
        if (strcmp($r, 'Invalid IP setup') == 0) {
            return ['result' => APIerror::InvalidIPSetup];
        }
        if (strcmp($r, 'Invalid') == 0) {
            return ['result' => APIerror::InvalidCurrency];
        }
        if (strcmp($r, 'The receiver is not valid') == 0) {
            return ['result' => APIerror::InvalidReceiver];
        }
        if (strcmp($r, 'Not enough money') == 0) {
            return ['result' => APIerror::NotEnoughMoney];
        }
        if (strcmp($r, 'limit') == 0) {
            return ['result' => APIerror::APILimitReached];
        }

        return ['result' => APIerror::OK, 'value' => $r];
    }

    public function TransferBTC($btcaddress, $amount, $currency, $memo)
    {
        $r = $this->client->transferbtc($this->username, $this->apiname, $this->password, $amount, $currency, $btcaddress, $memo);
        if (strcmp($r, 'Invalid user') == 0) {
            return ['result' => APIerror::InvalidUser];
        }
        if (strcmp($r, 'Invalid api data') == 0) {
            return ['result' => APIerror::InvalidAPIData];
        }
        if (strcmp($r, 'Invalid IP') == 0) {
            return ['result' => APIerror::InvalidIP];
        }
        if (strcmp($r, 'Invalid IP setup') == 0) {
            return ['result' => APIerror::InvalidIPSetup];
        }
        if (strcmp($r, 'Invalid') == 0) {
            return ['result' => APIerror::InvalidCurrency];
        }
        if (strcmp($r, 'The receiver is not valid') == 0) {
            return ['result' => APIerror::InvalidReceiver];
        }
        if (strcmp($r, 'Not enough money') == 0) {
            return ['result' => APIerror::NotEnoughMoney];
        }
        if (strcmp($r, 'limit') == 0) {
            return ['result' => APIerror::APILimitReached];
        }

        return ['result' => APIerror::OK, 'value' => $r];
    }

    public function TransferLTC($ltcaddress, $amount, $currency, $memo)
    {
        $r = $this->client->transferltc($this->username, $this->apiname, $this->password, $amount, $currency, $ltcaddress, $memo);
        if (strcmp($r, 'Invalid user') == 0) {
            return ['result' => APIerror::InvalidUser];
        }
        if (strcmp($r, 'Invalid api data') == 0) {
            return ['result' => APIerror::InvalidAPIData];
        }
        if (strcmp($r, 'Invalid IP') == 0) {
            return ['result' => APIerror::InvalidIP];
        }
        if (strcmp($r, 'Invalid IP setup') == 0) {
            return ['result' => APIerror::InvalidIPSetup];
        }
        if (strcmp($r, 'Invalid') == 0) {
            return ['result' => APIerror::InvalidCurrency];
        }
        if (strcmp($r, 'The receiver is not valid') == 0) {
            return ['result' => APIerror::InvalidReceiver];
        }
        if (strcmp($r, 'Not enough money') == 0) {
            return ['result' => APIerror::NotEnoughMoney];
        }
        if (strcmp($r, 'limit') == 0) {
            return ['result' => APIerror::APILimitReached];
        }

        return ['result' => APIerror::OK, 'value' => $r];
    }

    public function TransferDOGE($dogeaddress, $amount, $currency, $memo)
    {
        $r = $this->client->transferdoge($this->username, $this->apiname, $this->password, $amount, $currency, $dogeaddress, $memo);
        if (strcmp($r, 'Invalid user') == 0) {
            return ['result' => APIerror::InvalidUser];
        }
        if (strcmp($r, 'Invalid api data') == 0) {
            return ['result' => APIerror::InvalidAPIData];
        }
        if (strcmp($r, 'Invalid IP') == 0) {
            return ['result' => APIerror::InvalidIP];
        }
        if (strcmp($r, 'Invalid IP setup') == 0) {
            return ['result' => APIerror::InvalidIPSetup];
        }
        if (strcmp($r, 'Invalid') == 0) {
            return ['result' => APIerror::InvalidCurrency];
        }
        if (strcmp($r, 'The receiver is not valid') == 0) {
            return ['result' => APIerror::InvalidReceiver];
        }
        if (strcmp($r, 'Not enough money') == 0) {
            return ['result' => APIerror::NotEnoughMoney];
        }
        if (strcmp($r, 'limit') == 0) {
            return ['result' => APIerror::APILimitReached];
        }

        return ['result' => APIerror::OK, 'value' => $r];
    }

    public function TransferDRK($drkaddress, $amount, $currency, $memo)
    {
        $r = $this->client->transferdrk($this->username, $this->apiname, $this->password, $amount, $currency, $drkaddress, $memo);
        if (strcmp($r, 'Invalid user') == 0) {
            return ['result' => APIerror::InvalidUser];
        }
        if (strcmp($r, 'Invalid api data') == 0) {
            return ['result' => APIerror::InvalidAPIData];
        }
        if (strcmp($r, 'Invalid IP') == 0) {
            return ['result' => APIerror::InvalidIP];
        }
        if (strcmp($r, 'Invalid IP setup') == 0) {
            return ['result' => APIerror::InvalidIPSetup];
        }
        if (strcmp($r, 'Invalid') == 0) {
            return ['result' => APIerror::InvalidCurrency];
        }
        if (strcmp($r, 'The receiver is not valid') == 0) {
            return ['result' => APIerror::InvalidReceiver];
        }
        if (strcmp($r, 'Not enough money') == 0) {
            return ['result' => APIerror::NotEnoughMoney];
        }
        if (strcmp($r, 'limit') == 0) {
            return ['result' => APIerror::APILimitReached];
        }

        return ['result' => APIerror::OK, 'value' => $r];
    }

    public function TransferPPC($ppcaddress, $amount, $currency, $memo)
    {
        $r = $this->client->transferppc($this->username, $this->apiname, $this->password, $amount, $currency, $ppcaddress, $memo);
        if (strcmp($r, 'Invalid user') == 0) {
            return ['result' => APIerror::InvalidUser];
        }
        if (strcmp($r, 'Invalid api data') == 0) {
            return ['result' => APIerror::InvalidAPIData];
        }
        if (strcmp($r, 'Invalid IP') == 0) {
            return ['result' => APIerror::InvalidIP];
        }
        if (strcmp($r, 'Invalid IP setup') == 0) {
            return ['result' => APIerror::InvalidIPSetup];
        }
        if (strcmp($r, 'Invalid') == 0) {
            return ['result' => APIerror::InvalidCurrency];
        }
        if (strcmp($r, 'The receiver is not valid') == 0) {
            return ['result' => APIerror::InvalidReceiver];
        }
        if (strcmp($r, 'Not enough money') == 0) {
            return ['result' => APIerror::NotEnoughMoney];
        }
        if (strcmp($r, 'limit') == 0) {
            return ['result' => APIerror::APILimitReached];
        }

        return ['result' => APIerror::OK, 'value' => $r];
    }

    //To transfer BTC to multiple receivers and pay fee for once, pass btcaddress and amount parameters as array to the following function
    public function TransferToManyBTC($btcaddress, $amount, $currency, $memo)
    {
        $r = $this->client->transfertomanybtc($this->username, $this->apiname, $this->password, $amount, $currency, $btcaddress, $memo);
        if (strcmp($r, 'Invalid user') == 0) {
            return ['result' => APIerror::InvalidUser];
        }
        if (strcmp($r, 'Invalid api data') == 0) {
            return ['result' => APIerror::InvalidAPIData];
        }
        if (strcmp($r, 'Invalid IP') == 0) {
            return ['result' => APIerror::InvalidIP];
        }
        if (strcmp($r, 'Invalid IP setup') == 0) {
            return ['result' => APIerror::InvalidIPSetup];
        }
        if (strcmp($r, 'Invalid') == 0) {
            return ['result' => APIerror::InvalidCurrency];
        }
        if (strcmp($r, 'The receiver is not valid') == 0) {
            return ['result' => APIerror::InvalidReceiver];
        }
        if (strcmp($r, 'Not enough money') == 0) {
            return ['result' => APIerror::NotEnoughMoney];
        }
        if (strcmp($r, 'limit') == 0) {
            return ['result' => APIerror::APILimitReached];
        }

        return ['result' => APIerror::OK, 'value' => $r];
    }

    public function GetTransaction($TransActionID)
    {
        $r = $this->client->gettransaction($this->username, $this->apiname, $this->password, $TransActionID);
        if (strcmp($r, 'Invalid user') == 0) {
            return ['result' => APIerror::InvalidUser];
        }
        if (strcmp($r, 'Invalid api data') == 0) {
            return ['result' => APIerror::InvalidAPIData];
        }
        if (strcmp($r, 'Invalid IP') == 0) {
            return ['result' => APIerror::InvalidIP];
        }
        if (strcmp($r, 'Invalid IP setup') == 0) {
            return ['result' => APIerror::InvalidIPSetup];
        }
        if (strcmp($r, 'Invalid') == 0) {
            return ['result' => APIerror::Invalid];
        }

        return ['result' => APIerror::OK, 'value' => $r];
    }

    public function GetHistory($skip)
    {
        $r = $this->client->history($this->username, $this->apiname, $this->password, $skip);
        if (strcmp($r, 'Invalid user') == 0) {
            return ['result' => APIerror::InvalidUser];
        }
        if (strcmp($r, 'Invalid api data') == 0) {
            return ['result' => APIerror::InvalidAPIData];
        }
        if (strcmp($r, 'Invalid IP') == 0) {
            return ['result' => APIerror::InvalidIP];
        }
        if (strcmp($r, 'Invalid IP setup') == 0) {
            return ['result' => APIerror::InvalidIPSetup];
        }
        if (strcmp($r, 'Invalid') == 0) {
            return ['result' => APIerror::Invalid];
        }

        return ['result' => APIerror::OK, 'value' => $r];
    }

    public function GetNewTransActions()
    {
        $r = $this->client->getnewtransactions($this->username, $this->apiname, $this->password);
        if (strcmp($r, 'Invalid user') == 0) {
            return ['result' => APIerror::InvalidUser];
        }
        if (strcmp($r, 'Invalid api data') == 0) {
            return ['result' => APIerror::InvalidAPIData];
        }
        if (strcmp($r, 'Invalid IP') == 0) {
            return ['result' => APIerror::InvalidIP];
        }
        if (strcmp($r, 'Invalid IP setup') == 0) {
            return ['result' => APIerror::InvalidIPSetup];
        }
        if (strcmp($r, 'Updated') == 0) {
            return ['result' => APIerror::OK, 'value' => []];
        }

        return ['result' => APIerror::OK, 'value' => $r];
    }
}
