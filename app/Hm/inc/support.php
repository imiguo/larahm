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

if (app('data')->frm['action'] == 'send') {
    $from = ($userinfo['logged'] ? $userinfo['email'] : app('data')->frm['email']);
    $message = app('data')->settings['site_name'].' Support request from '.date('l dS of F Y h:i:s A').PHP_EOL;
    $message .= '---------------------------------------------------------------'.PHP_EOL;
    $message .= app('data')->frm['message'].PHP_EOL;
    $message .= '---------------------------------------------------------------'.PHP_EOL;
    $message .= 'User Additional Info :'.PHP_EOL;
    if ($userinfo['logged'] == 1) {
        $accounting = [];
        $accounting = get_user_balance($userinfo['id']);
        $message .= 'User      : '.$userinfo['username'].PHP_EOL;
        $message .= 'User Name : '.$userinfo['name'].PHP_EOL;
        $message .= 'E-Mail    : '.$userinfo['email'].PHP_EOL;
        if (psconfig('pm.marchant_id')) {
            $message .= 'PerfectMoney Acc : '.$userinfo['perfectmoney_account'].PHP_EOL;
        }
        if (psconfig('pe.shop_id')) {
            $message .= 'Payeer Acc : '.$userinfo['payeer_account'].PHP_EOL;
        }
        if (psconfig('as.user_name')) {
            $message .= 'Bitcoin Acc : '.$userinfo['bitcoin_account'].PHP_EOL;
        }

        $message .= 'Status    : '.$userinfo['status'].PHP_EOL;
        $message .= 'Active Deposits  : $'.sprintf('%.02f', $accounting['active_deposit']).PHP_EOL;
    } else {
        $message .= 'User Name : '.app('data')->frm['name'].PHP_EOL;
        $message .= 'E-Mail    : '.app('data')->frm['email'].PHP_EOL;
        $message .= 'Not Registered/Logged user'.PHP_EOL;
    }

    $message .= 'IP Address: '.$_SERVER['REMOTE_ADDR'].PHP_EOL;
    $message .= 'Language  : '.$_SERVER['HTTP_ACCEPT_LANGUAGE'].PHP_EOL;
    $q = 'select * from users where id = 1';
    $sth = db_query($q);
    $admin_email = '';
    while ($row = mysql_fetch_array($sth)) {
        $admin_email = $row['email'];
    }

    send_mail($admin_email, app('data')->settings['site_name'].' Support Request', $message, 'From: '.$from);
    throw new RedirectException('/?a=support&say=send');
}

  view_assign('token', $token);
  view_assign('say', app('data')->frm['say']);
  view_execute('support.blade.php');
