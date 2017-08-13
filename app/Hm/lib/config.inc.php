<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

ini_set('error_reporting', 'E_ALL & ~E_NOTICE & ~E_DEPRECATED');

require 'function.inc.php';

global $frm;
global $frm_cookie;
global $settings;
global $frm_env;
global $env_frm;
global $exchange_systems;
global $stats;

if (!extension_loaded('gd')) {
    $prefix = (PHP_SHLIB_SUFFIX == 'dll' ? 'php_' : '');
    dl($prefix.'gd.'.PHP_SHLIB_SUFFIX);
}

$get = $_GET;
$post = $_POST;
$frm = array_merge($get, $post);
$frm_orig = $frm;
$frm_cookie = $_COOKIE;
$gpc = ini_get('magic_quotes_gpc');
reset($frm);
while (list($kk, $vv) = each($frm)) {
    if (is_array($vv)) {
    } else {
        if ($gpc == '1') {
            $vv = str_replace('\\\'', '\'', $vv);
            $vv = str_replace('\\"', '"', $vv);
            $vv = str_replace('\\\\', '\\', $vv);
        }

        $vv = trim($vv);
        $vv_orig = $vv;
        $vv = strip_tags($vv);
    }

    $frm[$kk] = $vv;
    $frm_orig[$kk] = $vv_orig;
}

reset($frm_cookie);
while (list($kk, $vv) = each($frm_cookie)) {
    if (is_array($vv)) {
    } else {
        if ($gpc == '1') {
            $vv = str_replace('\\\'', '\'', $vv);
            $vv = str_replace('\\"', '"', $vv);
            $vv = str_replace('\\\\', '\\', $vv);
        }

        $vv = trim($vv);
        $vv = strip_tags($vv);
    }

    $frm_cookie[$kk] = $vv;
}

$frm_env = array_merge($_ENV, $_SERVER);
$frm_env['HTTP_HOST'] = preg_replace('/^www\./', '', $frm_env['HTTP_HOST']);

$referer = isset($frm_env['HTTP_REFERER']) ? $frm_env['HTTP_REFERER'] : null;
$host = $frm_env['HTTP_HOST'];
if (!strpos($referer, '//'.$host)) {
    setcookie('CameFrom', $referer, time() + 630720000);
}

$transtype = [
    'withdraw_pending'             => 'Withdrawal request',
    'add_funds'                    => 'Transfer from external processings',
    'deposit'                      => 'Deposit',
    'bonus'                        => 'Bonus',
    'penality'                     => 'Penalty',
    'earning'                      => 'Earning',
    'withdrawal'                   => 'Withdrawal',
    'commissions'                  => 'Referral commission',
    'early_deposit_release'        => 'Deposit release',
    'early_deposit_charge'         => 'Commission for an early deposit release',
    'release_deposit'              => 'Deposit returned to user account',
    'exchange_out'                 => ' Received on exchange',
    'exchange_in'                  => 'Spent on exchange',
    'exchange'                     => 'Exchange',
    'internal_transaction_spend'   => 'Spent on Internal Transaction',
    'internal_transaction_receive' => 'Received from Internal Transaction',
];
$exchange_systems = [
    0  => ['name' => 'e-gold', 'sfx' => 'egold'],
    2  => ['name' => 'INTGold', 'sfx' => 'intgold'],
    3  => ['name' => 'PerfectMoney', 'sfx' => 'perfectmoney'],
    4  => ['name' => 'StormPay', 'sfx' => 'stormpay'],
    5  => ['name' => 'e-Bullion', 'sfx' => 'ebullion'],
    6  => ['name' => 'PayPal', 'sfx' => 'paypal'],
    7  => ['name' => 'GoldMoney', 'sfx' => 'goldmoney'],
    8  => ['name' => 'eeeCurrency', 'sfx' => 'eeecurrency'],
    9  => ['name' => 'Pecunix', 'sfx' => 'pecunix'],
    10 => ['name' => 'Payeer', 'sfx' => 'payeer'],
    11 => ['name' => 'BitCoin', 'sfx' => 'bitcoin'],
];

$settings = get_settings();
foreach ($exchange_systems as $id => $data) {
    if (isset($settings['def_payee_account_'.$data['sfx']]) and $settings['def_payee_account_'.$data['sfx']] != '' and $settings['def_payee_account_'.$data['sfx']] != '0') {
        $exchange_systems[$id]['status'] = 1;
        continue;
    } else {
        $exchange_systems[$id]['status'] = 0;
        continue;
    }
}
$settings['site_url'] = (is_SSL() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];

$ip = $frm_env['REMOTE_ADDR'];
$time = time();
$url = $frm_env['REQUEST_URI'];
$agent = $frm_env['HTTP_USER_AGENT'];
$ret = db_query("insert hm2_visit (`ip`, `time`, `url`, `agent`) values('$ip', '$time', '$url', '$agent')");
