<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use App\Exceptions\EmptyException;

include app_path('Hm').'/lib/config.inc.php';

$mymd5 = app('data')->settings['md5altphrase'];
if (app('data')->frm['a'] == 'pay_withdraw') {
    $batch = app('data')->frm['PAYMENT_BATCH_NUM'];
    list($id, $str) = explode('-', app('data')->frm['withdraw']);
    $id = sprintf('%d', $id);
    if ($str == '') {
        $str = 'abcdef';
    }

    $str = quote($str);
    $q = 'select * from history where id = '.$id.' and str = \''.$str.'\' and type=\'withdraw_pending\'';
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
	ec = 0,
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
        $info['account'] = app('data')->frm['PAYEE_ACCOUNT'];
        $info['batch'] = $batch;
        $info['paying_batch'] = $batch;
        $info['receiving_batch'] = $batch;
        $info['currency'] = app('data')->exchange_systems[0]['name'];
        send_template_mail('withdraw_user_notification', $userinfo['email'], app('data')->settings['system_email'], $info);
    }

    echo 1;
    throw new EmptyException();
}

$hash = strtoupper(md5(app('data')->frm['PAYMENT_ID'].':'.app('data')->frm['PAYEE_ACCOUNT'].':'.app('data')->frm['PAYMENT_AMOUNT'].':'.app('data')->frm['PAYMENT_UNITS'].':'.app('data')->frm['PAYMENT_METAL_ID'].':'.app('data')->frm['PAYMENT_BATCH_NUM'].':'.app('data')->frm['PAYER_ACCOUNT'].':'.$mymd5.':'.app('data')->frm['ACTUAL_PAYMENT_OUNCES'].':'.app('data')->frm['USD_PER_OUNCE'].':'.app('data')->frm['FEEWEIGHT'].':'.app('data')->frm['TIMESTAMPGMT']));
if (($hash == strtoupper(app('data')->frm['V2_HASH']) and app('data')->exchange_systems[0]['status'] == 1)) {
    $ip = app('data')->env['REMOTE_ADDR'];
    if (! preg_match('/63\\.240\\.230\\.\\d/i', $ip)) {
        throw new EmptyException();
    }

    $user_id = sprintf('%d', app('data')->frm['userid']);
    $h_id = sprintf('%d', app('data')->frm['hyipid']);
    $compound = sprintf('%d', app('data')->frm['compound']);
    $amount = app('data')->frm['PAYMENT_AMOUNT'];
    $batch = app('data')->frm['PAYMENT_BATCH_NUM'];
    $account = app('data')->frm['PAYER_ACCOUNT'];
    if (((app('data')->frm['a'] == 'checkpayment' and app('data')->frm['PAYMENT_METAL_ID'] == 1) and app('data')->frm['PAYMENT_UNITS'] == 1)) {
        add_deposit(0, $user_id, $amount, $batch, $account, $h_id, $compound);
    }
}

echo '1';
