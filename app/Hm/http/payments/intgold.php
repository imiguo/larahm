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

$mymd5 = app('data')->settings['md5altphrase_intgold'];
if (app('data')->frm['CUSTOM2'] == 'pay_withdraw') {
    $batch = app('data')->frm['TRANSACTION_ID'];
    list($id, $str) = explode('-', app('data')->frm['CUSTOM1']);
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
	ec = 2,
	date = now()
	';
        db_query($q);
        $q = 'select * from users where id = '.$row['user_id'];
        $sth = db_query($q);
        $userinfo = mysql_fetch_array($sth);
        $info = [];
        $info['username'] = $userinfo['username'];
        $info['name'] = $userinfo['name'];
        $info['amount'] = sprintf('%.02f', abs($row['amount']));
        $info['account'] = app('data')->frm['SELLERACCOUNTID'];
        $info['batch'] = $batch;
        $info['paying_batch'] = $batch;
        $info['receiving_batch'] = $batch;
        $info['currency'] = app('data')->exchange_systems[2]['name'];
        send_template_mail('withdraw_user_notification', $userinfo['email'], app('data')->settings['system_email'], $info);
    }

    echo 1;
    throw new EmptyException();
}

if (($mymd5 == app('data')->frm['HASH'] and (app('data')->frm['TRANSACTION_ID'] != '' and app('data')->exchange_systems[2]['status'] == 1))) {
    if (app('data')->frm['RESULT'] != '0') {
        throw new EmptyException();
    }

    $user_id = sprintf('%d', app('data')->frm['ITEM_NUMBER']);
    $h_id = sprintf('%d', app('data')->frm['CUSTOM2']);
    $compound = sprintf('%d', app('data')->frm['CUSTOM4']);
    $amount = app('data')->frm['AMOUNT'];
    $batch = app('data')->frm['TRANSACTION_ID'];
    $account = app('data')->frm['BUYERACCOUNTID'];
    if (app('data')->frm['CUSTOM3'] == 'checkpayment') {
        add_deposit(2, $user_id, $amount, $batch, $account, $h_id, $compound);
    }
}

echo '1';
