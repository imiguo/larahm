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

$mymd5 = $settings['md5altphrase'];
if ($frm['a'] == 'pay_withdraw') {
    $batch = $frm['PAYMENT_BATCH_NUM'];
    list($id, $str) = explode('-', $frm['withdraw']);
    $id = sprintf('%d', $id);
    if ($str == '') {
        $str = 'abcdef';
    }

    $str = quote($str);
    $q = 'select * from hm2_history where id = '.$id.' and str = \''.$str.'\' and type=\'withdraw_pending\'';
    $sth = db_query($q);
    while ($row = mysql_fetch_array($sth)) {
        $q = 'delete from hm2_history where id = '.$id;
        db_query($q);
        $q = 'insert into hm2_history set
	user_id = '.$row['user_id'].',
	amount = -'.abs($row['amount']).(''.',
	type = \'withdrawal\',
	description = \'Withdraw processed. Batch id = '.$batch.'\',
	actual_amount = -').abs($row['amount']).',
	ec = 0,
	date = now()
	';
        db_query($q);
        $q = 'select * from hm2_users where id = '.$row['user_id'];
        $usth = db_query($q);
        $userinfo = mysql_fetch_array($usth);
        $info = [$user];
        $info['username'] = $userinfo['username'];
        $info['name'] = $userinfo['name'];
        $info['amount'] = sprintf('%.02f', abs($row['amount']));
        $info['account'] = $frm['PAYEE_ACCOUNT'];
        $info['batch'] = $batch;
        $info['paying_batch'] = $batch;
        $info['receiving_batch'] = $batch;
        $info['currency'] = $exchange_systems[0]['name'];
        send_template_mail('withdraw_user_notification', $userinfo['email'], $settings['system_email'], $info);
    }

    echo 1;
    throw new EmptyException();
}

$hash = strtoupper(md5($frm['PAYMENT_ID'].':'.$frm['PAYEE_ACCOUNT'].':'.$frm['PAYMENT_AMOUNT'].':'.$frm['PAYMENT_UNITS'].':'.$frm['PAYMENT_METAL_ID'].':'.$frm['PAYMENT_BATCH_NUM'].':'.$frm['PAYER_ACCOUNT'].':'.$mymd5.':'.$frm['ACTUAL_PAYMENT_OUNCES'].':'.$frm['USD_PER_OUNCE'].':'.$frm['FEEWEIGHT'].':'.$frm['TIMESTAMPGMT']));
if (($hash == strtoupper($frm['V2_HASH']) and $exchange_systems[0]['status'] == 1)) {
    $ip = $frm_env['REMOTE_ADDR'];
    if (!preg_match('/63\\.240\\.230\\.\\d/i', $ip)) {
        throw new EmptyException();
    }

    $user_id = sprintf('%d', $frm['userid']);
    $h_id = sprintf('%d', $frm['hyipid']);
    $compound = sprintf('%d', $frm['compound']);
    $amount = $frm['PAYMENT_AMOUNT'];
    $batch = $frm['PAYMENT_BATCH_NUM'];
    $account = $frm['PAYER_ACCOUNT'];
    if ((($frm['a'] == 'checkpayment' and $frm['PAYMENT_METAL_ID'] == 1) and $frm['PAYMENT_UNITS'] == 1)) {
        add_deposit(0, $user_id, $amount, $batch, $account, $h_id, $compound);
    }
}

echo '1';
