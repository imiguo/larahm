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

list($action, $user_id, $h_id) = preg_split('/\\|/', $frm['custom']);
if ($action == 'pay_withdraw') {
    $batch = $frm['txn_id'];
    list($id, $str) = explode('-', $user_id);
    $id = sprintf('%d', $id);
    if ($str == '') {
        $str = 'abcdef';
    }

    $str = quote($str);
    $q = 'select * from hm2_history where id = '.$id.' and str = \''.$str.'\'';
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
	ec = 6,
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
        $info['account'] = $frm['business'];
        $info['batch'] = $batch;
        $info['paying_batch'] = $batch;
        $info['receiving_batch'] = $batch;
        $info['currency'] = $exchange_systems[6]['name'];
        send_template_mail('withdraw_user_notification', $userinfo['email'], $settings['system_email'], $info);
    }

    $admin_url = env('ADMIN_URL');
    header("Location: {$admin_url}?a=pay_withdraw&say=yes");
    exit();
}

if (function_exists('curl_init')) {
    $req = 'cmd=_notify-validate';
    foreach ($frm as $key => $value) {
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
    if ((((($res == 'VERIFIED' and $frm['payment_status'] == 'Completed') and $frm['business'] == $settings['def_payee_account_paypal']) and $frm['mc_currency'] == 'USD') and $exchange_systems[6]['status'] == 1)) {
        $user_id = sprintf('%d', $user_id);
        $h_id = sprintf('%d', $h_id);
        $compound = sprintf('%d', $frm['compound']);
        $amount = $frm['mc_gross'];
        $batch = $frm['txn_id'];
        $account = $frm['payer_email'];
        if ($action == 'checkpayment') {
            add_deposit(6, $user_id, $amount, $batch, $account, $h_id, $compound);
            header('Location: /?a=return_egold&process=yes');
            exit();
        }
    }
}

header('Location: /?a=return_egold&process=no');
