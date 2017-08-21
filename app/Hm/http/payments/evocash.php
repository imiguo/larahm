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

$mymd5 = app('data')->settings['md5altphrase_evocash'];
if (app('data')->frm['a'] == 'pay_withdraw') {
    $batch = app('data')->frm['receivingtransactionid'];
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
	ec = 1,
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
        $info['account'] = app('data')->frm['receivingaccountid'];
        $info['batch'] = app('data')->frm['payingtransactionid'].'/'.app('data')->frm['receivingtransactionid'];
        $info['paying_batch'] = app('data')->frm['payingtransactionid'];
        $info['receiving_batch'] = app('data')->frm['receivingtransactionid'];
        $info['currency'] = app('data')->exchange_systems[1]['name'];
        send_template_mail('withdraw_user_notification', $userinfo['email'], app('data')->settings['system_email'], $info);
    }

    echo 1;
    throw new EmptyException();
}

$hash = strtoupper(md5(app('data')->frm['payingaccountid'].':'.app('data')->frm['receivingaccountid'].':'.app('data')->frm['payingtransactionid'].':'.app('data')->frm['receivingtransactionid'].':'.app('data')->frm['amount'].':'.strtoupper(md5(app('data')->settings['md5altphrase_evocash'])).':'.app('data')->frm['timestampgmt']));
if (($hash == strtoupper(app('data')->frm['merchanthashcheck']) and app('data')->exchange_systems[1]['status'] == 1)) {
    $user_id = sprintf('%d', app('data')->frm['userid']);
    $h_id = sprintf('%d', app('data')->frm['hyipid']);
    $compound = sprintf('%d', app('data')->frm['compound']);
    $amount = app('data')->frm['amount'];
    $batch = app('data')->frm['receivingtransactionid'];
    $account = app('data')->frm['payingaccountid'];
    if (app('data')->frm['a'] == 'checkpayment') {
        add_deposit(1, $user_id, $amount, $batch, $account, $h_id, $compound);
    }
}

echo '1';
