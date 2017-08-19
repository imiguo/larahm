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

$mymd5 = app('data')->settings['md5altphrase_goldmoney'];
if (app('data')->frm['a'] == 'pay_withdraw') {
    $batch = app('data')->frm['OMI_TXN_ID'];
    list($id, $str) = explode('-', app('data')->frm['withdraw']);
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
	ec = 7,
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
        $info['account'] = app('data')->frm['OMI_MERCHANT_HLD_NO'];
        $info['batch'] = $batch;
        $info['paying_batch'] = $batch;
        $info['receiving_batch'] = $batch;
        $info['currency'] = app('data')->exchange_systems[7]['name'];
        send_template_mail('withdraw_user_notification', $userinfo['email'], app('data')->settings['system_email'], $info);
    }

    echo 1;
    throw new EmptyException();
}

if (app('data')->frm['OMI_MODE'] != 'LIVE') {
    echo '1';
    throw new EmptyException();
}

$hash = strtoupper(md5(app('data')->frm['OMI_MERCHANT_REF_NO'].'?'.app('data')->frm['OMI_MODE'].'?'.app('data')->frm['OMI_MERCHANT_HLD_NO'].'?'.app('data')->frm['OMI_PAYER_HLD_NO'].'?'.app('data')->frm['OMI_CURRENCY_CODE'].'?'.app('data')->frm['OMI_CURRENCY_AMT'].'?'.app('data')->frm['OMI_GOLDGRAM_AMT'].'?'.app('data')->frm['OMI_TXN_ID'].'?'.app('data')->frm['OMI_TXN_DATETIME'].'?'.$mymd5));
if (($hash == strtoupper(app('data')->frm['OMI_HASH']) and app('data')->exchange_systems[7]['status'] == 1)) {
    $user_id = sprintf('%d', app('data')->frm['userid']);
    $h_id = sprintf('%d', app('data')->frm['hyipid']);
    $compound = sprintf('%d', app('data')->frm['compound']);
    $amount = app('data')->frm['OMI_CURRENCY_AMT'];
    $batch = app('data')->frm['OMI_TXN_ID'];
    $account = app('data')->frm['OMI_PAYER_HLD_NO'];
    if ((app('data')->frm['a'] == 'checkpayment' and app('data')->frm['OMI_CURRENCY_CODE'] == 840)) {
        add_deposit(7, $user_id, $amount, $batch, $account, $h_id, $compound);
    }
}

echo '1';
