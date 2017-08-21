<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use App\Exceptions\RedirectException;

list($action, $user_id, $h_id) = preg_split('/\\|/', app('data')->frm['custom']);
if ($action == 'pay_withdraw') {
    $batch = app('data')->frm['txn_id'];
    list($id, $str) = explode('-', $user_id);
    $id = sprintf('%d', $id);
    if ($str == '') {
        $str = 'abcdef';
    }

    $str = quote($str);
    $q = 'select * from history where id = '.$id.' and str = \''.$str.'\'';
    $sth = db_query($q);
    while ($row = mysql_fetch_array($sth)) {
        $q = 'delete from history where id = '.$id;
        db_query($q);
        $q = 'insert into history set
	user_id = '.$row['user_id'].',
	amount = -'.abs($row['amount']).(''.',
	type = \'withdrawal\',
	description = \'Withdraw processed. Batch id = '.$batch.'\',
	actual_amount = -').abs($row['amount']).',
	ec = 6,
	date = now()
	';
        db_query($q);
        $q = 'select * from users where id = '.$row['user_id'];
        $usth = db_query($q);
        $userinfo = mysql_fetch_array($usth);
        $info = [$user];
        $info['username'] = $userinfo['username'];
        $info['name'] = $userinfo['name'];
        $info['amount'] = sprintf('%.02f', abs($row['amount']));
        $info['account'] = app('data')->frm['business'];
        $info['batch'] = $batch;
        $info['paying_batch'] = $batch;
        $info['receiving_batch'] = $batch;
        $info['currency'] = app('data')->exchange_systems[6]['name'];
        send_template_mail('withdraw_user_notification', $userinfo['email'], app('data')->settings['system_email'], $info);
    }

    throw new RedirectException('/?a=pay_withdraw&say=yes');
}

if (function_exists('curl_init')) {
    $req = 'cmd=_notify-validate';
    foreach (app('data')->frm as $key => $value) {
        $value = urlencode(stripslashes($value));
        $req .= '&'.$key.'='.$value;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.paypal.com/cgi-bin/webscr');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $res = curl_exec($ch);
    curl_close($ch);
    if ((((($res == 'VERIFIED' and app('data')->frm['payment_status'] == 'Completed') and app('data')->frm['business'] == app('data')->settings['def_payee_account_paypal']) and app('data')->frm['mc_currency'] == 'USD') and app('data')->exchange_systems[6]['status'] == 1)) {
        $user_id = sprintf('%d', $user_id);
        $h_id = sprintf('%d', $h_id);
        $compound = sprintf('%d', app('data')->frm['compound']);
        $amount = app('data')->frm['mc_gross'];
        $batch = app('data')->frm['txn_id'];
        $account = app('data')->frm['payer_email'];
        if ($action == 'checkpayment') {
            add_deposit(6, $user_id, $amount, $batch, $account, $h_id, $compound);
            throw new RedirectException('/?a=return_egold&process=yes');
        }
    }
}

throw new RedirectException('/?a=return_egold&process=no');
