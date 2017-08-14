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

$mymd5 = $settings['md5altphrase_intgold'];
if ($frm['CUSTOM2'] == 'pay_withdraw') {
    $batch = $frm['TRANSACTION_ID'];
    list($id, $str) = explode('-', $frm['CUSTOM1']);
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
	ec = 2,
	date = now()
	';
        db_query($q);
        $q = 'select * from hm2_users where id = '.$row['user_id'];
        $sth = db_query($q);
        $userinfo = mysql_fetch_array($sth);
        $info = [];
        $info['username'] = $userinfo['username'];
        $info['name'] = $userinfo['name'];
        $info['amount'] = sprintf('%.02f', abs($row['amount']));
        $info['account'] = $frm['SELLERACCOUNTID'];
        $info['batch'] = $batch;
        $info['paying_batch'] = $batch;
        $info['receiving_batch'] = $batch;
        $info['currency'] = $exchange_systems[2]['name'];
        send_template_mail('withdraw_user_notification', $userinfo['email'], $settings['system_email'], $info);
    }

    echo 1;
    throw new EmptyException();
}

if (($mymd5 == $frm['HASH'] and ($frm['TRANSACTION_ID'] != '' and $exchange_systems[2]['status'] == 1))) {
    if ($frm['RESULT'] != '0') {
        throw new EmptyException();
    }

    $user_id = sprintf('%d', $frm['ITEM_NUMBER']);
    $h_id = sprintf('%d', $frm['CUSTOM2']);
    $compound = sprintf('%d', $frm['CUSTOM4']);
    $amount = $frm['AMOUNT'];
    $batch = $frm['TRANSACTION_ID'];
    $account = $frm['BUYERACCOUNTID'];
    if ($frm['CUSTOM3'] == 'checkpayment') {
        add_deposit(2, $user_id, $amount, $batch, $account, $h_id, $compound);
    }
}

echo '1';
