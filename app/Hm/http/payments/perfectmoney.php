<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

include HM_PATH.'/lib/config.inc.php';

file_put_contents('../log/perfectmoney_processing_'.ENV.'.txt', json_encode($frm).PHP_EOL, FILE_APPEND);
file_put_contents('../log/perfectmoney_processing_'.ENV.'.txt', 'IP:'.$frm_env['REMOTE_ADDR'].PHP_EOL, FILE_APPEND);

$mymd5 = $settings['md5altphrase_perfectmoney'];
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
    exit();
}

if ($frm['a'] == 'checkpayment') {
    $string = $frm['PAYMENT_ID'].':'.$frm['PAYEE_ACCOUNT'].':'.
              $frm['PAYMENT_AMOUNT'].':'.$frm['PAYMENT_UNITS'].':'.
              $frm['PAYMENT_BATCH_NUM'].':'.
              $frm['PAYER_ACCOUNT'].':'.$mymd5.':'.
              $frm['TIMESTAMPGMT'];
    $hash = strtoupper(md5($string));

    if ($hash == $frm['V2_HASH'] and $exchange_systems[3]['status'] == 1) {
        // $ip = $frm_env['REMOTE_ADDR'];
        // if ( ! preg_match('/63\\.240\\.230\\.\\d/i', $ip)) {
        //     exit;
        // }

        $user_id = sprintf('%d', $frm['PAYMENT_ID']);
        $h_id = sprintf('%d', $frm['plan_id']);
        $compound = sprintf('%d', $frm['compound']);
        $amount = $frm['PAYMENT_AMOUNT'];
        $batch = $frm['PAYMENT_BATCH_NUM'];
        $account = $frm['PAYER_ACCOUNT'];
        if ($frm['PAYMENT_UNITS'] == 'USD') {
            add_deposit(3, $user_id, $amount, $batch, $account, $h_id, $compound);
        }
    }

    echo '1';
}
