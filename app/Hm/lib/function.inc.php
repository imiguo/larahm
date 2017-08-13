<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

function is_SSL()
{
    if (!isset($_SERVER['HTTPS'])) {
        return false;
    }
    if ($_SERVER['HTTPS'] === 1) {  //Apache
        return true;
    } elseif ($_SERVER['HTTPS'] === 'on') { //IIS
        return true;
    } elseif ($_SERVER['SERVER_PORT'] == 443) { //其他
        return true;
    }

    return false;
}

function send_mail()
{
    $to = func_get_arg(0);
    $subject = func_get_arg(1);
    $message = func_get_arg(2);
    $time = time();
    $sth = db_query("select time from hm2_sendmails where `to` = '$to' and `status` = 1 order by `time` desc limit 1");
    $row = mysql_fetch_assoc($sth);
    $status = 0;
    if (!isset($row['time']) || (time() - $row['time'] > 60)) {
        call_user_func_array('mail', func_get_args());
        $status = 1;
    }
    $subject = mysql_real_escape_string($subject);
    $message = mysql_real_escape_string($message);
    db_query("insert hm2_sendmails (`to`, `subject`, `message`, `time`, `status`) values ('$to', '$subject', '$message', '$time', '$status')");
}

function add_log($subject, $message)
{
    $time = time();
    $subject = mysql_real_escape_string($subject);
    db_query("insert hm2_logs (`subject`, `message`, `time`) values('$subject', '$message', '$time')");
}

function genarate_token()
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-.';
    $mask = substr(str_shuffle(str_repeat($chars, 5)), 0, 8);

    return $mask;
}

function db_query($q)
{
    $time = time();
    if (strpos($q, 'select') !== 0) {
        $insert = mysql_real_escape_string($q);
        mysql_query("insert into hm2_queries (`query`, `time`) values ('$insert', '$time')");
    }

    return mysql_query($q);
}

function add_deposit($ec, $user_id, $amount, $batch, $account, $h_id, $compound)
{
    global $settings;
    global $exchange_systems;
    $compound = intval($compound);
    $h_id = intval($h_id);
    $user_id = intval($user_id);
    $amount = sprintf('%.02f', $amount);
    $batch_found = 0;
    $q = 'select count(*) as cnt from hm2_history where ec = '.$ec.' && type = \'add_funds\' && description like \'%Batch id = '.$batch.'\'';
    $sth = db_query($q);
    $row = mysql_fetch_array($sth);
    if (0 < $row['cnt']) {
        $batch_found = 1;
    }

    if ($batch_found == 1) {
        return 0;
    }

    $desc = 'Add funds to account from '.$exchange_systems[$ec]['name'].('. Batch id = '.$batch);
    if ($ec == 4) {
        $desc = 'Add funds to account from '.$exchange_systems[$ec]['name'].(' '.$amount.' - StormPay Fee. Batch id = '.$batch);
        $amount = $amount - $amount * 6.9 / 100 - 0.69;
    }

    $q = 'insert into hm2_history set
        	user_id = '.$user_id.',
        	amount = \''.$amount.'\',
        	type = \'add_funds\',
        	description = \''.$desc.'\',
        	actual_amount = '.$amount.',
        	ec = '.$ec.',
        	date = now()
        	';
    db_query($q);
    $q = 'select * from hm2_types where id = '.$h_id;
    $sth = db_query($q);
    $name = '';
    $type = mysql_fetch_array($sth);
    $delay = -1;
    if ($type) {
        $delay += $row[delay];
        $name = quote($type['name']);
        if ($type['use_compound'] == 0) {
            $compound = 0;
        } else {
            if ($type['compound_max_deposit'] == 0) {
                $type['compound_max_deposit'] = $amount + 1;
            }

            if (($type['compound_min_deposit'] <= $amount and $amount <= $type['compound_max_deposit'])) {
                if ($type['compound_percents_type'] == 1) {
                    $cps = preg_split('/\\s*,\\s*/', $type['compound_percents']);
                    if (!in_array($compound, $cps)) {
                        $compound = $cps[0];
                    }
                } else {
                    if ($compound < $type['compound_min_percent']) {
                        $compound = $type['compound_min_percent'];
                    }

                    if ($type['compound_max_percent'] < $compound) {
                        $compound = $type['compound_max_percent'];
                    }
                }
            } else {
                $compound = 0;
            }
        }
    }

    if ($delay < 0) {
        $delay = 0;
    }

    $q = 'select min(hm2_plans.min_deposit) as min, max(if(hm2_plans.max_deposit = 0, 999999999999, hm2_plans.max_deposit)) as max from hm2_types left outer join hm2_plans on hm2_types.id = hm2_plans.parent where hm2_types.id = '.$h_id;
    $sth1 = db_query($q);
    $row1 = mysql_fetch_array($sth1);
    $min_deposit = $row1['min'];
    $max_deposit = $row1['max'];
    if (($min_deposit <= $amount and $amount <= $max_deposit)) {
        $q = 'insert into hm2_deposits set
          	user_id = '.$user_id.',
          	type_id = '.$h_id.',
          	deposit_date = now(),
          	last_pay_date = now()+ interval '.$delay.' day,
          	status = \'on\',
          	q_pays = 0,
          	amount = \''.$amount.'\',
          	actual_amount = \''.$amount.'\',
          	ec = '.$ec.',
          	compound = '.$compound.'
          	';
        db_query($q);
        $deposit_id = mysql_insert_id();
        $q = 'insert into hm2_history set
          	user_id = '.$user_id.',
          	amount = \'-'.$amount.'\',
          	type = \'deposit\',
          	description = \'Deposit to '.quote($name).('\',
          	actual_amount = -'.$amount.',
          	ec = '.$ec.',
          	date = now(),
                deposit_id = '.$deposit_id.'
          	');
        db_query($q);
        if ($settings['banner_extension'] == 1) {
            $imps = 0;
            if (0 < $settings['imps_cost']) {
                $imps = $amount * 1000 / $settings['imps_cost'];
            }

            if (0 < $imps) {
                $q = 'update hm2_users set imps = imps + '.$imps.' where id = '.$user_id;
                db_query($q);
            }
        }

        $ref_sum = referral_commission($user_id, $amount, $ec);
    } else {
        $name = 'Deposit to Account';
    }

    $q = 'select * from hm2_users where id = '.$user_id;
    $sth = db_query($q);
    $user = mysql_fetch_array($sth);
    $info = [$user];
    $info['username'] = $user['username'];
    $info['name'] = $user['name'];
    $info['amount'] = number_format($amount, 2);
    $info['account'] = $account;
    $info['currency'] = $exchange_systems[$ec]['name'];
    $info['batch'] = $batch;
    $info['compound'] = $compound;
    $info['plan'] = $name;
    $info['ref_sum'] = $ref_sum;
    $q = 'select email from hm2_users where id = 1';
    $sth = db_query($q);
    $admin_email = '';
    while ($row = mysql_fetch_array($sth)) {
        $admin_email = $row['email'];
    }

    if ($user['is_test'] != 1) {
        send_template_mail('deposit_admin_notification', $admin_email, $settings['system_email'], $info);
        send_template_mail('deposit_user_notification', $user[email], $settings['system_email'], $info);
    }

    return 1;
}

function referral_commission($user_id, $amount, $ec)
{
    global $settings;
    global $exchange_systems;
    $ref_sum = 0;
    if ($settings['use_referal_program'] == 1) {
        $q = 'select * from hm2_users where id = '.$user_id;
        $rsth = db_query($q);
        $uinfo = mysql_fetch_array($rsth);
        $ref = 0;
        if (0 < $uinfo['ref']) {
            $ref = $uinfo['ref'];
        } else {
            return 0;
        }

        if ($settings['pay_active_referal']) {
            $q = 'select count(*) as cnt from hm2_deposits where user_id = '.$ref;
            $sth = db_query($q);
            $row = mysql_fetch_array($sth);
            if ($row['cnt'] <= 0) {
                return 0;
            }
        }

        if ($settings['use_solid_referral_commission'] == 1) {
            if (0 < $settings['solid_referral_commission_amount']) {
                $q = 'select count(*) as cnt from hm2_deposits where user_id = '.$user_id;
                $sth = db_query($q);
                $row = mysql_fetch_array($sth);
                if ($row['cnt'] == 1) {
                    $sum = $settings['solid_referral_commission_amount'];
                    $ref_sum += $sum;
                    $q = 'insert into hm2_history set
    		user_id = '.$ref.',
    		amount = '.$sum.',
    		actual_amount = '.$sum.',
    		type = \'commissions\',
    		description = \'Referral commission from '.$uinfo['username'].('\',
    		ec = '.$ec.',
    		date = now()');
                    db_query($q);
                    $q = 'select * from hm2_users where id = '.$ref;
                    $rsth = db_query($q);
                    $refinfo = mysql_fetch_array($rsth);
                    $refinfo['amount'] = number_format($sum, 2);
                    $refinfo['ref_username'] = $uinfo['username'];
                    $refinfo['ref_name'] = $uinfo['name'];
                    $refinfo['currency'] = $exchange_systems[$ec]['name'];
                    send_template_mail('referral_commision_notification', $refinfo['email'], $settings['system_email'],
                        $refinfo);
                }
            }
        } else {
            if ($settings['use_active_referal'] == 1) {
                $q = 'select count(distinct user_id) as col from hm2_users, hm2_deposits where ref = '.$ref.' and hm2_deposits.user_id = hm2_users.id';
            } else {
                $q = 'select count(*) as col from hm2_users where ref = '.$ref;
            }

            $sth = db_query($q);
            if ($row = mysql_fetch_array($sth)) {
                $col = $row['col'];
                $q = 'select percent from hm2_referal where from_value <= '.$col.' and (to_value >= '.$col.' or to_value = 0) order by from_value desc limit 1';
                $sth = db_query($q);
                if ($row = mysql_fetch_array($sth)) {
                    $sum = $amount * $row['percent'] / 100;
                    $ref_sum += $sum;
                    $q = 'insert into hm2_history set
    		user_id = '.$ref.',
    		amount = '.$sum.',
    		actual_amount = '.$sum.',
    		type = \'commissions\',
    		description = \'Referral commission from '.$uinfo['username'].('\',
    		ec = '.$ec.',
    		date = now()');
                    db_query($q);
                    $q = 'select * from hm2_users where id = '.$ref;
                    $rsth = db_query($q);
                    $refinfo = mysql_fetch_array($rsth);
                    $refinfo['amount'] = number_format($sum, 2);
                    $refinfo['ref_username'] = $uinfo['username'];
                    $refinfo['ref_name'] = $uinfo['name'];
                    $refinfo['currency'] = $exchange_systems[$ec]['name'];
                    send_template_mail('referral_commision_notification', $refinfo['email'], $settings['system_email'],
                        $refinfo);
                }
            }
        }

        if ($settings['use_solid_referral_commission'] != 1) {
            for ($i = 2; $i < 11; ++$i) {
                if (($ref == 0 or $settings['ref'.$i.'_cms'] == 0)) {
                    break;
                }

                $q = 'select * from hm2_users where id = '.$ref;
                $sth = db_query($q);
                $ref = 0;
                while ($row = mysql_fetch_array($sth)) {
                    $ref = $row['ref'];
                    if (0 < $ref) {
                        $sum = $amount * $settings['ref'.$i.'_cms'] / 100;
                        $ref_sum += $sum;
                        $q = 'insert into hm2_history set
                  user_id = '.$row['ref'].(',
                  amount = '.$sum.',
                  actual_amount = '.$sum.',
                  type = \'commissions\',
                  description = \'Referral commission from ').$uinfo['username'].(' '.$i.' level referral\',
                  ec = '.$ec.',
                  date = now()');
                        db_query($q);
                        continue;
                    }
                }
            }
        }
    }

    return $ref_sum;
}

function send_money_to_perfectmoney($e_password, $amount, $account, $memo, $error_txt)
{
    global $settings;

    if ($account == 0) {
        $q = 'insert into hm2_pay_errors set date = now(), txt = \'Can`t process withdrawal to Perfect-Money account 0.\'';
        db_query($q);

        return [0, 'Invalid Perfect-Money account', ''];
    }

    if ($e_password == '') {
        $q = 'select v from hm2_pay_settings where n=\'perfectmoney_account_password\'';
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            $perfectmoney_password = decode_pass_for_mysql($row['v']);
        }
    } else {
        $perfectmoney_password = $e_password;
    }

    $ch = curl_init();
    $memo = rawurlencode($memo);
    $params = [
        'AccountID'     => $settings['perfectmoney_from_account_id'],
        'PassPhrase'    => $perfectmoney_password,
        'Payer_Account' => $settings['perfectmoney_from_account'],
        'Payee_Account' => $account,
        'Amount'        => $amount,
        'PAY_IN'        => '1',
        'Memo'          => $memo,
    ];
    curl_setopt($ch, CURLOPT_URL, 'https://perfectmoney.is/acct/confirm.asp');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $a = curl_exec($ch);
    curl_close($ch);

    if (preg_match_all("/<input name='(.*)' type='hidden' value='(.*)'>/", $a, $parts, PREG_SET_ORDER)) {
        return [1, '', $parts[1]];
    } else {
        $e = quote($error_txt.' '.$a);
        $q = 'insert into hm2_pay_errors set date = now(), txt = \''.$e.'\'';
        db_query($q);

        return [0, $error_txt.(' '.$a), ''];
    }
}

function send_money_to_egold($e_password, $amount, $account, $memo, $error_txt)
{
    global $settings;

    if ($account == 0) {
        $q = 'insert into hm2_pay_errors set date = now(), txt = \'Can`t process withdrawal to E-Gold account 0.\'';
        db_query($q);

        return [0, 'Invalid E-Gold account', ''];
    }

    if ($e_password == '') {
        $q = 'select v from hm2_pay_settings where n=\'egold_account_password\'';
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            $egold_password = decode_pass_for_mysql($row['v']);
        }
    } else {
        $egold_password = $e_password;
    }

    $ch = curl_init();
    $memo = rawurlencode($memo);
    $params = [
        'AccountID'          => $settings['egold_from_account'],
        'PassPhrase'         => $egold_password,
        'Payee_Account'      => $account,
        'Amount'             => $amount,
        'PAY_IN'             => 1,
        'WORTH_OF'           => 'Gold',
        'Memo'               => $memo,
        'IGNORE_RATE_CHANGE' => 'y',
    ];
    curl_setopt($ch, CURLOPT_URL, 'https://www.e-gold.com/acct/confirm.asp');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $a = curl_exec($ch);
    curl_close($ch);
    $parts = [];
    if (preg_match('/<input type=hidden name=PAYMENT_BATCH_NUM VALUE="(\\d+)">/ims', $a, $parts)) {
        return [1, '', $parts[1]];
    } else {
        if (preg_match('/<input type=hidden name=ERROR VALUE="(.*?)">/ims', $a, $parts)) {
            $txt = preg_replace('/&lt;/i', '<', $parts[1]);
            $txt = preg_replace('/&gt;/i', '>', $txt);
            $e = quote($error_txt.' '.$txt);
            $q = 'insert into hm2_pay_errors set date = now(), txt = \''.$e.'\'';
            db_query($q);

            return [0, $error_txt.(' '.$txt), ''];
        } else {
            $e = quote($error_txt.' Unknown error');
            $q = 'insert into hm2_pay_errors set date = now(), txt = \''.$e.'\'';
            db_query($q);

            return [0, $error_txt.' Unknown error', ''];
        }
    }
}

function send_money_to_evocash($e_password, $amount, $account, $memo, $error_txt)
{
    global $settings;
    $amount = sprintf('%0.2f', floor($amount * 100) / 100);

    if ($account == 0) {
        $q = 'insert into hm2_pay_errors set date = now(), txt = \'Can not process withdrawal to Evocash account 0.\'';
        db_query($q);

        return [0, 'Invalid EvoCash account', ''];
    }

    if ($e_password == '') {
        $q = 'select v from hm2_pay_settings where n=\'evocash_account_password\'';
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            $evocash_password = decode_pass_for_mysql($row['v']);
        }

        $q = 'select v from hm2_pay_settings where n=\'evocash_transaction_code\'';
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            $evocash_code = decode_pass_for_mysql($row['v']);
        }
    } else {
        list($evocash_password, $evocash_code) = preg_split('/\\|/', $e_password);
    }

    $ch = curl_init();
    $memo = rawurlencode($memo);
    curl_setopt($ch, CURLOPT_URL,
        'https://www.evocash.com/evoswift/instantpayment.cfm?payingaccountid='.$settings['evocash_from_account'].'&username='.$settings['evocash_username'].('&password='.$evocash_password.'&transaction_code=').$evocash_code.('&amount='.$amount.'&reference=&memo='.$memo.'&receivingaccountid='.$account));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $a = curl_exec($ch);
    curl_close($ch);
    $parts = [];
    if (preg_match('/<INPUT TYPE="Hidden" NAME="PayingTransactionID" VALUE="(.*?)">/ims', $a, $parts)) {
        return [1, '', $parts[1]];
    } else {
        if (preg_match('/<INPUT TYPE="Hidden" NAME="Error" VALUE="(.*?)">/ims', $a, $parts)) {
            $txt = $parts[1];
            $e = quote($error_txt.' '.$txt);
            $q = 'insert into hm2_pay_errors set date = now(), txt = \''.$e.'\'';
            db_query($q);

            return [0, $error_txt.(' '.$txt), ''];
        } else {
            $e = quote($error_txt.' Unknown error');
            $q = 'insert into hm2_pay_errors set date = now(), txt = \''.$e.'\'';
            db_query($q);

            return [0, $error_txt.' Unknown error', ''];
        }
    }
}

function send_money_to_intgold($e_password, $amount, $account, $memo, $error_txt)
{
    global $settings;

    if ($account == 0) {
        $q = 'insert into hm2_pay_errors set date = now(), txt = \'Can`t process withdrawal to IntGold account 0.\'';
        db_query($q);

        return [0, 'Invalid IntGold account', ''];
    }

    if ($e_password == '') {
        $q = 'select v from hm2_pay_settings where n=\'intgold_password\'';
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            $intgold_password = decode_pass_for_mysql($row['v']);
        }

        $q = 'select v from hm2_pay_settings where n=\'intgold_transaction_code\'';
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            $intgold_code = decode_pass_for_mysql($row['v']);
        }
    } else {
        list($intgold_password, $intgold_code) = preg_split('/\\|/', $e_password);
    }

    $ch = curl_init();
    $memo = rawurlencode($memo);
    curl_setopt($ch, CURLOPT_URL, 'https://intgold.com/cgi-bin/autopay.cgi');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)');
    curl_setopt($ch, CURLOPT_POSTFIELDS,
        'ACCOUNTID='.$settings['intgold_from_account'].'&PASSWORD='.$intgold_password.'&SECPASSWORD='.$intgold_code.'&RECEIVER='.$account.('&AMOUNT='.$amount.'&NOTE='.$memo));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $a = curl_exec($ch);
    curl_close($ch);
    $parts = [];
    if (preg_match('/Success\\s*TRANSACTION_ID:(.*?)$/ims', $a, $parts)) {
        return [1, '', $parts[1]];
    } else {
        $e = quote($error_txt.' '.$a);
        $q = 'insert into hm2_pay_errors set date = now(), txt = \''.$e.'\'';
        db_query($q);

        return [0, $error_txt.(' '.$a), ''];
    }
}

function send_money_to_eeecurrency($e_password, $amount, $account, $memo, $error_txt)
{
    global $settings;

    if ($account == 0) {
        $q = 'insert into hm2_pay_errors set date = now(), txt = \'Can`t process withdrawal to eeeCureency account 0.\'';
        db_query($q);

        return [0, 'Invalid eeeCurrency account', ''];
    }

    if ($e_password == '') {
        $q = 'select v from hm2_pay_settings where n=\'eeecurrency_password\'';
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            $eeecurrency_password = decode_pass_for_mysql($row['v']);
        }

        $q = 'select v from hm2_pay_settings where n=\'eeecurrency_transaction_code\'';
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            $eeecurrency_code = decode_pass_for_mysql($row['v']);
        }
    } else {
        list($eeecurrency_password, $eeecurrency_code) = preg_split('/\\|/', $e_password);
    }

    $ch = curl_init();
    $memo = rawurlencode($memo);
    curl_setopt($ch, CURLOPT_URL, 'https://eeecurrency.com/cgi-bin/autopay.cgi');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)');
    curl_setopt($ch, CURLOPT_POSTFIELDS,
        'ACCOUNTID='.$settings['eeecurrency_from_account'].'&PASSWORD='.$eeecurrency_password.'&SECPASSWORD='.$eeecurrency_code.'&RECEIVER='.$account.('&AMOUNT='.$amount.'&NOTE='.$memo));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $a = curl_exec($ch);
    curl_close($ch);
    $parts = [];
    if (preg_match('/Success\\s*TRANSACTION_ID:(.*?)$/ims', $a, $parts)) {
        return [1, '', $parts[1]];
    } else {
        $e = quote($error_txt.' '.$a);
        $q = 'insert into hm2_pay_errors set date = now(), txt = \''.$e.'\'';
        db_query($q);

        return [0, $error_txt.(' '.$a), ''];
    }
}

function send_money_to_pecunix($e_password, $amount, $account, $memo, $error_txt)
{
    global $settings;

    if ($account == 0) {
        $q = 'insert into hm2_pay_errors set date = now(), txt = \'Can`t process withdrawal to Pecunix account 0.\'';
        db_query($q);

        return [0, 'Invalid Pecunix account', ''];
    }

    if ($e_password == '') {
        $q = 'select v from hm2_pay_settings where n=\'pecunix_password\'';
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            $pecunix_password = decode_pass_for_mysql($row['v']);
        }
    } else {
        $pecunix_password = $e_password[0];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://pxi.pecunix.com/');
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $a = curl_exec($ch);
    curl_close($ch);
    preg_match('/Date: \\w+, \\d+ \\w+ \\d+ (\\d+)/', $a, $m);
    $hour = $m[1];
    $token = strtoupper(md5($pecunix_password.':'.gmdate('Ymd').(':'.$hour)));
    $data = '
    <TransferRequest>
      <Transfer>
        <TransferId> </TransferId>
        <Payer> '.$settings['pecunix_from_account'].' </Payer>
        <Payee> '.$account.' </Payee>
        <CurrencyId> GAU </CurrencyId>
        <Equivalent>
          <CurrencyId> USD </CurrencyId>
          <Amount> '.$amount.' </Amount>
        </Equivalent>
        <FeePaidBy> Payee </FeePaidBy>
        <Memo> '.$memo.' </Memo>
      </Transfer>
      <Auth>
        <Token> '.$token.' </Token>
      </Auth>
    </TransferRequest>
    ';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://pxi.pecunix.com/money.refined...transfer');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $a = curl_exec($ch);
    curl_close($ch);
    $out = parsexml_pecunix($a);
    if ($out['status'] == 'ok') {
        return [1, '', $out['batch']];
    } else {
        if ($out['status'] == 'error') {
            $e = quote($error_txt.' '.$out['text'].' '.$out['additional']);
            $q = 'insert into hm2_pay_errors set date = now(), txt = \''.$e.'\'';
            db_query($q);

            return [0, $e, ''];
        } else {
            $e = quote($error_txt.' Parse error: '.$a);
            $q = 'insert into hm2_pay_errors set date = now(), txt = \''.$e.'\'';
            db_query($q);

            return [0, $e, ''];
        }
    }
}

function send_money_to_ebullion($dump, $amount, $account, $memo, $error_txt)
{
    global $settings;

    if ($account == '') {
        $q = 'insert into hm2_pay_errors set date = now(), txt = \'Can`t process withdrawal to e-Bullion account 0.\'';
        db_query($q);

        return [0, 'Invalid e-Bullion account', ''];
    }

    $payment = '<atip.batch.v1><payment.list>';
    $payment = $payment.'<payment>';
    $payment = $payment.'<payer>'.$settings['def_payee_account_ebullion'].'</payer>';
    $payment = $payment.'<payee>'.$account.'</payee>';
    $payment = $payment.'<amount>'.$amount.'</amount>';
    $payment = $payment.'<metal>1</metal>';
    $payment = $payment.'<unit>1</unit>';
    $payment = $payment.'<memo>'.$memo.'</memo>';
    $payment = $payment.'<ref></ref>';
    $payment = $payment.'</payment>';
    $payment = $payment.'</payment.list></atip.batch.v1>';
    $infile = tempnam('', 'in.');
    $outfile = tempnam('', 'out.');
    $fd = fopen($infile, 'w');
    fwrite($fd, $payment);
    fclose($fd);
    $atippath = CACHE_PATH;
    $gpg_path = escapeshellcmd($settings['gpg_path']);
    $passphrase = decode_pass_for_mysql($settings['md5altphrase_ebullion']);
    $atip_status_url = $settings['site_url'];
    $gpg_options = ' --yes --no-tty --no-secmem-warning --no-options --no-default-keyring --batch --homedir '.$atippath.' --keyring=pubring.gpg --secret-keyring=secring.gpg --armor --throw-keyid --always-trust --passphrase-fd 0';
    $gpg_command = 'echo \''.$passphrase.'\' | '.$gpg_path.' '.$gpg_options.' --recipient A20077\\@e-bullion.com --local-user '.$settings['def_payee_account_ebullion'].('\\@e-bullion.com --output '.$outfile.' --sign --encrypt '.$infile.' 2>&1');
    $buf = '';
    $fp = popen($gpg_command, 'r');
    while (!feof($fp)) {
        $buf = fgets($fp, 4096);
    }

    pclose($fp);
    $fd = fopen($outfile, 'r');
    $atip_batch_msg = fread($fd, filesize($outfile));
    fclose($fd);
    unlink($infile);
    unlink($outfile);
    $qs = 'ATIP_ACCOUNT='.$settings['def_payee_account_ebullion'].'&ATIP_BATCH_MSG='.rawurlencode($atip_batch_msg).'&ATIP_STATUS_URL='.rawurlencode($atip_status_url);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://atip.e-bullion.com/batch.php?'.$qs);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    $a = curl_exec($ch);
    curl_close($ch);
    $matches = [];
    $verification = '';
    if (preg_match('/Location: .*?\\?ATIP_VERIFICATION=([^\\r\\n]+)%0A/', $a, $matches)) {
        $verification = $matches[1];
    }

    $verification = urldecode($verification);
    $xmlfile = tempnam('', 'xml.cert.');
    $tmpfile = tempnam('', 'xml.tmp.');
    $fd = fopen($tmpfile, 'w');
    fwrite($fd, $verification);
    fclose($fd);
    $gpg_options = ' --yes --no-tty --no-secmem-warning --no-options --no-default-keyring --batch --homedir '.$atippath.' --keyring=pubring.gpg --secret-keyring=secring.gpg --armor --passphrase-fd 0';
    $gpg_command = 'echo \''.$passphrase.'\' | '.$gpg_path.' '.$gpg_options.' --output '.$xmlfile.' --decrypt '.$tmpfile.' 2>&1';
    $buf = '';
    $keyID = '';
    $fp = popen($gpg_command, 'r');
    while (!feof($fp)) {
        $buf = fgets($fp, 4096);
        $pos = strstr($buf, 'key ID');
        if (0 < strlen($pos)) {
            $keyID = preg_replace('/[\\n\\r]/', '', substr($pos, 7));
            continue;
        }
    }

    pclose($fp);
    if ($keyID == $settings['ebullion_keyID']) {
        if (is_file($xmlfile)) {
            $fx = fopen($xmlfile, 'r');
            $xmlcert = fread($fx, filesize($xmlfile));
            fclose($fx);
        } else {
            $e = quote($error_txt.' Can not found decrypted verification response!');
            $q = 'insert into hm2_pay_errors set date = now(), txt = \''.$e.'\'';
            db_query($q);

            return [0, $error_txt.' Can not found decrypted verification response!', ''];
        }

        $data = parsexml($xmlcert);
        if ($data['status'] == 'ok') {
            return [1, '', $data['batch']];
        } else {
            if ($data['status'] == 'error') {
                $e = quote($error_txt.' '.$data['text'].' '.$data['additional']);
                $q = 'insert into hm2_pay_errors set date = now(), txt = \''.$e.'\'';
                db_query($q);

                return [0, $error_txt.$data['text'].' '.$data['additional']];
            } else {
                $e = quote($error_txt.' Unknown error');
                $q = 'insert into hm2_pay_errors set date = now(), txt = \''.$e.'\'';
                db_query($q);

                return [0, $error_txt.' Unknown error', ''];
            }
        }
    } else {
        $e = quote($error_txt.' Can not decrypt verification response! ');
        $q = 'insert into hm2_pay_errors set date = now(), txt = \''.$e.'\'';
        db_query($q);

        return [0, $error_txt.' Can not decrypt verification response!', ''];
    }

    unlink($tmpfile);
    unlink($xmlfile);
}

function getelement($data, $element)
{
    $element = strtolower($element);
    $elementlen = strlen($element) + 2;
    if ($startat = strpos($data, '<'.$element.'>') === false) {
        return '';
    }

    if ($endat = strpos($data, '</'.$element.'>') === false) {
        return '';
    }

    $value = trim(substr($data, $startat + $elementlen, $endat - ($startat + $elementlen)));

    return $value;
}

function parsexml($xmlcert)
{
    $out = [];
    $balancelist = getelement($xmlcert, 'balanceresponse.list');
    if ($balancelist != '') {
        $out['status'] = 'balance';
        $done = false;
        if ($starttemp = stristr($balancelist, '<balance>') === false) {
            $done = true;
        } else {
            $startat = strlen($balancelist) - strlen($starttemp);
        }

        if ($endtemp = stristr($balancelist, '</balance>') === false) {
            $done = true;
        } else {
            $endat = strlen($balancelist) - strlen($endtemp);
        }

        while (!$done) {
            $Balance = trim(substr($balancelist, $startat + 9, $endat - 9));
            $balancelist = trim(substr($balancelist, $endat + 10));
            $out['amount'] = getelement($Balance, 'amount');
            if ($starttemp = stristr($balancelist, '<balance>') === false) {
                $done = true;
            } else {
                $startat = strlen($balancelist) - strlen($starttemp);
            }

            if ($endtemp = stristr($balancelist, '</balance>') === false) {
                $done = true;
                continue;
            } else {
                $endat = strlen($balancelist) - strlen($endtemp);
                continue;
            }
        }
    }

    $verifylist = getelement($xmlcert, 'verified.list');
    if ($verifylist != '') {
        $out['status'] = 'ok';
        $done = false;
        if ($starttemp = stristr($verifylist, '<transaction>') === false) {
            $done = true;
        } else {
            $startat = strlen($verifylist) - strlen($starttemp);
        }

        if ($endtemp = stristr($verifylist, '</transaction>') === false) {
            $done = true;
        } else {
            $endat = strlen($verifylist) - strlen($endtemp);
        }

        while (!$done) {
            $Verify = trim(substr($verifylist, $startat + 13, $endat - 13));
            $verifylist = trim(substr($verifylist, $endat + 14));
            $out['batch'] = getelement($Verify, 'id');
            $out['payee'] = getelement($Verify, 'payee');
            $out['payer'] = getelement($Verify, 'payer');
            $out['amount'] = getelement($Verify, 'amount');
            $out['metal'] = getelement($Verify, 'metal');
            $out['unit'] = getelement($Verify, 'unit');
            $out['memo'] = getelement($Verify, 'memo');
            $out['exchange'] = getelement($Verify, 'exchange');
            $out['fee'] = getelement($Verify, 'fee');
            if ($starttemp = stristr($verifylist, '<transaction>') === false) {
                $done = true;
            } else {
                $startat = strlen($verifylist) - strlen($starttemp);
            }

            if ($endtemp = stristr($verifylist, '</transaction>') === false) {
                $done = true;
                continue;
            } else {
                $endat = strlen($verifylist) - strlen($endtemp);
                continue;
            }
        }
    }

    $failedlist = getelement($xmlcert, 'failed.list');
    if ($failedlist != '') {
        $out['status'] = 'error';
        $done = false;
        if ($starttemp = stristr($failedlist, '<failed>') === false) {
            $done = true;
        } else {
            $startat = strlen($failedlist) - strlen($starttemp);
        }

        if ($endtemp = stristr($failedlist, '</failed>') === false) {
            $done = true;
        } else {
            $endat = strlen($failedlist) - strlen($endtemp);
        }

        while (!$done) {
            $Failed = trim(substr($failedlist, $startat + 13, $endat - 13));
            $failedlist = trim(substr($failedlist, $endat + 14));
            $out['text'] = getelement($Failed, 'error');
            if ($starttemp = stristr($failedlist, '<failed>') === false) {
                $done = true;
            } else {
                $startat = strlen($failedlist) - strlen($starttemp);
            }

            if ($endtemp = stristr($failedlist, '</failed>') === false) {
                $done = true;
                continue;
            } else {
                $endat = strlen($failedlist) - strlen($endtemp);
                continue;
            }
        }
    }

    $errorlist = getelement($xmlcert, 'errorresponse.list');
    if ($errorlist != '') {
        $out['status'] = 'error';
        $done = false;
        if ($starttemp = stristr($errorlist, '<errorresponse>') === false) {
            $done = true;
        } else {
            $startat = strlen($errorlist) - strlen($starttemp);
        }

        if ($endtemp = stristr($errorlist, '</errorresponse>') === false) {
            $done = true;
        } else {
            $endat = strlen($errorlist) - strlen($endtemp);
        }

        while (!$done) {
            $ErrorResponse = trim(substr($errorlist, $startat + 15, $endat - 15));
            $errdone = false;
            if ($starterr = stristr($ErrorResponse, '<error>') === false) {
                $errdone = true;
            } else {
                $starterrat = strlen($ErrorResponse) - strlen($starterr);
            }

            if ($enderr = stristr($ErrorResponse, '</error>') === false) {
                $errdone = true;
            } else {
                $enderrat = strlen($ErrorResponse) - strlen($enderr);
            }

            while (!$errdone) {
                $Error = trim(substr($ErrorResponse, $starterrat + 7, $enderrat - 7));
                $ErrorResponse = trim(substr($ErrorResponse, $enderrat + 8));
                $out['text'] = getelement($Error, 'text');
                $out['additional'] = getelement($Error, 'additional');
                if ($starterr = stristr($ErrorResponse, '<error>') === false) {
                    $errdone = true;
                } else {
                    $starterrat = strlen($ErrorResponse) - strlen($starterr);
                }

                if ($enderr = stristr($ErrorResponse, '</error>') === false) {
                    $errdone = true;
                    continue;
                } else {
                    $enderrat = strlen($ErrorResponse) - strlen($enderr);
                    continue;
                }
            }

            $errorlist = trim(substr($errorlist, $endat + 16));
            if ($starttemp = stristr($errorlist, '<errorresponse>') === false) {
                $done = true;
            } else {
                $startat = strlen($errorlist) - strlen($starttemp);
            }

            if ($endtemp = stristr($errorlist, '</errorresponse>') === false) {
                $done = true;
                continue;
            } else {
                $endat = strlen($errorlist) - strlen($endtemp);
                continue;
            }
        }
    }

    return $out;
}

function getelement_pecunix($data, $element)
{
    $elementlen = strlen($element) + 2;
    $pos1 = strpos($data, '<'.$element.' ');
    $pos2 = strpos($data, '<'.$element.'>');
    if ($pos1 !== false) {
        $startat = $pos1;
    }

    if ($pos2 !== false) {
        $startat = $pos2;
    }

    if ($startat === false) {
        return '';
    }

    if ($endat = strpos($data, '</'.$element.'>') === false) {
        return '';
    }

    $startendat = strpos($data, '>', $startat);
    $value = trim(substr($data, $startendat + 1, $endat - ($startat + $elementlen)));

    return $value;
}

function parsexml_pecunix($xmlcert)
{
    $out = [];
    $verifylist = getelement_pecunix($xmlcert, 'Receipt');
    if ($verifylist != '') {
        $out['status'] = 'ok';
        $Verify = $verifylist;
        $out['batch'] = getelement_pecunix($Verify, 'ReceiptId');
        $out['payer'] = getelement_pecunix($Verify, 'Payer');
        $out['payee'] = getelement_pecunix($Verify, 'Payee');
        $Equivalent = getelement_pecunix($Verify, 'Equivalent');
        $out['amount'] = getelement_pecunix($Equivalent, 'Amount');
        $out['currency'] = getelement_pecunix($Equivalent, 'CurrencyId');
    }

    $errorlist = getelement_pecunix($xmlcert, 'ErrorResponse');
    if ($errorlist != '') {
        $out['status'] = 'error';
        $Error = $errorlist;
        $out['text'] = getelement_pecunix($Error, 'Text');
        $out['additional'] = getelement_pecunix($Error, 'Additional');
    }

    return $out;
}

function encode_pass_for_mysql($string)
{
    $ret = base64_encode($string);
    $a = preg_split('//', $ret);
    $b = preg_split('//', md5($string).base64_encode($string));
    $ret = '';
    for ($i = 0; $i < count($a); ++$i) {
        $ret = $ret.$a[$i].$b[$i];
    }

    $ret = str_replace('=', '!!!^', $ret);

    return $ret;
}

function decode_pass_for_mysql($string)
{
    $string = str_replace('!!!^', '=', $string);
    $a = preg_split('//', $string);
    $string = '';
    for ($i = 0; $i < count($a); $i += 2) {
        $string .= $a[$i - 1];
    }

    $ret = base64_decode($string);

    return $ret;
}

function send_template_mail($email_id, $to, $from, $info)
{
    global $settings;
    $q = 'select * from hm2_emails where id = \''.$email_id.'\'';
    $sth = db_query($q);
    $row = mysql_fetch_array($sth);
    if (!$row) {
        return;
    }

    if (!$row['status']) {
        return;
    }

    $text = $row['text'];
    $subject = $row['subject'];
    foreach ($info as $k => $v) {
        if (is_array($v)) {
            $v = $v[0];
        }

        $text = preg_replace('/#'.$k.'#/', $v, $text);
        $subject = preg_replace('/#'.$k.'#/', $v, $subject);
    }

    $text = preg_replace('/#site_name#/', $settings['site_name'], $text);
    $subject = preg_replace('/#site_name#/', $settings['site_name'], $subject);
    $text = preg_replace('/#site_url#/', $settings['site_url'], $text);
    $subject = preg_replace('/#site_url#/', $settings['site_url'], $subject);
    if ($settings[site_name] == 'free') {
        $fh = fopen('mails.txt', 'a');
        fwrite($fh, 'TO: '.$to.'
From: '.$from.'
Subject: '.$subject.'

'.$text.'

');
        fclose($fh);
    } else {
        send_mail($to, $subject, $text, 'From: '.$from.'
Reply-To: '.$from);
    }
}

function start_info_table($size = '100%')
{
    return '
<table cellspacing=0 cellpadding=1 border=0 width='.$size.' bgcolor=#FF8D00>
<tr><td bgcolor=#FF8D00>
<table cellspacing=0 cellpadding=0 border=0 width=100%>
<tr>
<td valign=top width=10 bgcolor=#FFFFF2><img src=images/sign.gif></td>
<td valign=top bgcolor=#FFFFF2 style=\'padding: 10px; color: #D20202; font-family: verdana; font-size: 11px;\'>';
}

function end_info_table()
{
    return '</td></tr></table></td></tr></table>';
}

/**
 * 没有用到这个函数.
 *
 * @param $deposit_id
 * @param $amount
 */
function pay_direct_return_deposit($deposit_id, $amount)
{
    global $settings;
    if ($settings['use_auto_payment'] == 1) {
        $q = 'select * from hm2_deposits where id = '.$deposit_id;
        $sth = db_query($q);
        $dep = mysql_fetch_array($sth);
        $q = 'select * from hm2_users where id = '.$dep['user_id'];
        $sth = db_query($q);
        $user = mysql_fetch_array($sth);
        if ($user['auto_withdraw'] != 1) {
            return;
        }

        $q = 'select * from hm2_types where id = '.$dep['type_id'];
        $sth = db_query($q);
        $type = mysql_fetch_array($sth);
        $amount = abs($amount);
        $success_txt = 'Return principal from deposit $'.$amount.'. Auto-withdrawal to '.$user['username'].' from '.$settings['site_name'];
        $error_txt = 'Auto-withdrawal error, tried to return '.$amount.' to e-gold account # '.$user['egold_account'].'. Error:';
        list($res, $text, $batch) = send_money_to_egold('', $amount, $user['egold_account'], $success_txt, $error_txt);
        if ($res == 1) {
            $q = 'insert into hm2_history set
          	user_id = '.$user['id'].(',
            amount = -'.$amount.',
          	actual_amount = -'.$amount.',
            type=\'withdrawal\',
          	date = now(),
  		description = \'Auto-withdrawal retuned deposit to account ').$user['egold_account'].('. Batch is '.$batch.'\'');
            db_query($q);
        }
    }
}

function pay_direct_earning($deposit_id, $amount, $date)
{
    global $settings;
    global $exchange_systems;

    if ($settings['demomode'] == 1) {
        return;
    }

    if ($settings['use_auto_payment'] == 1) {
        $q = 'select * from hm2_deposits where id = '.$deposit_id;
        $sth = db_query($q);
        $dep = mysql_fetch_array($sth);
        if (!in_array($dep[ec], [0, 1, 2, 5])) {
            return;
        }

        $q = 'select * from hm2_users where id = '.$dep['user_id'];
        $sth = db_query($q);
        $user = mysql_fetch_array($sth);
        if (($user['admin_auto_pay_earning'] != 1 or $user['user_auto_pay_earning'] != 1)) {
            return;
        }

        $amount = abs($amount);
        $fee = floor($amount * $settings['withdrawal_fee']) / 100;
        if ($fee < $settings['withdrawal_fee_min']) {
            $fee = $settings['withdrawal_fee_min'];
        }

        $to_withdraw = $amount - $fee;
        if ($to_withdraw < 0) {
            $to_withdraw = 0;
        }

        $to_withdraw = sprintf('%.02f', floor($to_withdraw * 100) / 100);
        $success_txt = 'Earning from deposit $'.$dep['actual_amount'].'. Auto withdraw to '.$user['username'].' from '.$settings['site_name'];
        if ($dep[ec] == 0) {
            $error_txt = 'Auto-withdrawal error, tried to send '.$to_withdraw.' earning to e-gold account # '.$user['egold_account'].'. Error:';
            list($res, $text, $batch) = send_money_to_egold('', $to_withdraw, $user['egold_account'], $success_txt,
                $error_txt);
        }

        if ($dep[ec] == 1) {
            $error_txt = 'Error, tried to send '.$to_withdraw.' to Evocash account # '.$user['evocash_account'].'. Error:';
            list($res, $text, $batch) = send_money_to_evocash('', $to_withdraw, $user['evocash_account'], $success_txt,
                $error_txt);
        }

        if ($dep[ec] == 2) {
            $error_txt = 'Error, tried to send '.$to_withdraw.' to IntGold account # '.$user['intgold_account'].'. Error:';
            list($res, $text, $batch) = send_money_to_intgold('', $to_withdraw, $user['intgold_account'], $success_txt,
                $error_txt);
        }

        if ($dep[ec] == 3) {
            $error_txt = 'Error, tried to send '.$to_withdraw.' to Perfect Money account # '.$user['perfectmoney_account'].'. Error:';
            list($res, $text, $batch) = send_money_to_perfectmoney('', $to_withdraw, $user['perfectmoney_account'], $success_txt,
                $error_txt);
        }

        if ($dep[ec] == 5) {
            $error_txt = 'Error, tried to send '.$to_withdraw.' to e-Bullion account # '.$user['intgold_account'].'. Error:';
            list($res, $text, $batch) = send_money_to_ebullion('', $to_withdraw, $user['ebullion_account'],
                $success_txt, $error_txt);
        }

        if ($dep[ec] == 8) {
            $error_txt = 'Error, tried to send '.$to_withdraw.' to eeeCurrency account # '.$user['eeecurrency_account'].'. Error:';
            list($res, $text, $batch) = send_money_to_eeecurrency('', $to_withdraw, $user['eeecurrency_account'],
                $success_txt, $error_txt);
        }

        if ($dep[ec] == 9) {
            $error_txt = 'Error, tried to send '.$to_withdraw.' to Pecunix account # '.$user['pecunix_account'].'. Error:';
            list($res, $text, $batch) = send_money_to_pecunix('', $to_withdraw, $user['pecunix_account'], $success_txt,
                $error_txt);
        }

        if ($res == 1) {
            $d_account = [
                $user['egold_account'],
                $user['evocash_account'],
                $user['intgold_account'],
                '',
                $user['stormpay_account'],
                $user['ebullion_account'],
                $user['paypal_account'],
                $user['goldmoney_account'],
                $user['eeecurrency_account'],
                $user['pecunix_account'],
            ];
            $q = 'insert into hm2_history set
            user_id = '.$user['id'].(',
        		amount = -'.$amount.',
            		actual_amount = -'.$amount.',
        		type=\'withdrawal\',
            		'.$date.',
			ec = ').$dep['ec'].',
        		description = \'Earning to account auto-withdrawal'.$d_account[$dep[ec]].('. Batch is '.$batch.'\'');
            db_query($q);
            $info = [];
            $info['username'] = $user['username'];
            $info['name'] = $user['name'];
            $info['amount'] = $amount;
            $info['batch'] = $batch;
            $info['account'] = $d_account[$dep[ec]];
            $info['currency'] = $exchange_systems[$dep['ec']]['name'];
            send_template_mail('withdraw_user_notification', $user['email'], $settings['system_email'], $info);
        }
    }
}

function count_earning($u_id)
{
    global $settings;
    $types = [];
    if (($settings['use_crontab'] == 1 and $u_id != -2)) {
        return;
    }

    $q = 'select hm2_plans.* from hm2_plans, hm2_types where hm2_types.status = \'on\' and hm2_types.id = hm2_plans.parent order by parent, min_deposit';
    $sth = db_query($q);
    while ($row = mysql_fetch_array($sth)) {
        $types[$row['parent']][$row['id']] = $row;
    }

    $lines = 1;
    $u_cond = 'u.last_access_time + interval 30 minute < now() ';
    if ($u_id == -1) {
        $u_cond = '1 = 1';
        $q = 'select * from hm2_users where l_e_t + interval 15 minute < now() and status = \'on\'';
    } else {
        $q = 'select * from hm2_users where id = '.$u_id.' and status = \'on\'';
    }

    if ($u_id == -2) {
        $q = 'select * from hm2_users where status = \'on\'';
        $q = 'select distinct user_id as id from hm2_deposits where to_days(last_pay_date) < to_days(now()) order by user_id';
    }

    ($sth_users = db_query($q));
    while ($row_user = mysql_fetch_array($sth_users)) {
        $row_user_id = $row_user['id'];
        $q = 'update hm2_users set l_e_t = now() where id = '.$row_user_id;
        db_query($q);
        $lines = 1;
        while (0 < $lines) {
            $q = 'select
              d.*,
              t.period as period, t.use_compound as use_compound,
              t.compound_min_deposit, t.compound_max_deposit,
              t.compound_min_percent, t.compound_max_percent,
              t.compound_percents_type, t.compound_percents,
              t.work_week as work_week,
              t.q_days as q_days, t.withdraw_principal,
              (d.deposit_date + interval t.withdraw_principal_duration day < now()) wp_ok,
              t.return_profit as return_profit
            from
              hm2_deposits as d,
              hm2_types as t,
              hm2_users as u
            where
              u.id = '.$row_user_id.' and
              u.status = \'on\' and
              d.status=\'on\' and
              d.type_id = t.id and
              t.status = \'on\' and
              u.id = d.user_id and
              (t.q_days > to_days(d.last_pay_date) - to_days(d.deposit_date) or t.q_days = 0) and
              (
                (d.last_pay_date + interval 1 day <= now() and t.period = \'d\')or
                (d.last_pay_date + interval 7 day <= now() and t.period = \'w\') or
                (d.last_pay_date + interval 14 day <= now() and t.period = \'b-w\') or
                (d.last_pay_date + interval 1 month <= now() and t.period = \'m\') or
                (d.last_pay_date + interval 2 month <= now() and t.period = \'2m\') or
                (d.last_pay_date + interval 3 month <= now() and t.period = \'3m\') or
                (d.last_pay_date + interval 6 month <= now() and t.period = \'6m\') or
                (d.last_pay_date + interval 1 year <= now() and t.period = \'y\') or
                (d.deposit_date + interval t.q_days day <= now() and t.period = \'end\')
              ) and
              ((t.q_days = 0) or
                (
                (d.deposit_date + interval t.q_days day >= d.last_pay_date  and t.period = \'d\') or
                (d.deposit_date + interval t.q_days day >= d.last_pay_date + interval 7 day and t.period = \'w\') or
                (d.deposit_date + interval t.q_days day >= d.last_pay_date + interval 14 day  and t.period = \'b-w\') or
                (d.deposit_date + interval t.q_days day >= d.last_pay_date + interval 1 month  and t.period = \'m\') or
                (d.deposit_date + interval t.q_days day >= d.last_pay_date + interval 2 month  and t.period = \'2m\') or
                (d.deposit_date + interval t.q_days day >= d.last_pay_date + interval 3 month  and t.period = \'3m\') or
                (d.deposit_date + interval t.q_days day >= d.last_pay_date + interval 6 month  and t.period = \'6m\') or
                (d.deposit_date + interval t.q_days day >= d.last_pay_date + interval 1 year and t.period = \'y\') or
                (t.q_days > 0 and t.period = \'end\')
              ))';
            $sth = db_query($q);
            $lines = 0;
            while ($row = mysql_fetch_array($sth)) {
                ++$lines;
                if (!isset($types[$row['type_id']])) {
                    continue;
                }

                $percent = 0;
                $last_percent = 0;
                reset($types);
                reset($types[$row['type_id']]);
                while (list($key, $plan) = each($types[$row['type_id']])) {
                    if (($plan['min_deposit'] <= $row['actual_amount'] and ($row['actual_amount'] <= $plan['max_deposit'] or $plan['max_deposit'] == 0))) {
                        $percent = $plan['percent'];
                    }

                    if (($row['actual_amount'] < $plan['min_deposit'] and $percent == 0)) {
                        $percent = $last_percent;
                    }

                    if (($row['actual_amount'] < $plan['min_deposit'] and 0 < $percent)) {
                        break;
                    }

                    $last_percent = $plan['percent'];
                }

                if (($plan['max_deposit'] != 0 and $plan['max_deposit'] < $row['actual_amount'])) {
                    $percent = $last_percent;
                }

                $inc = $row['actual_amount'] * $percent / 100;
                $interval = '';
                if ($row['period'] == 'd') {
                    $interval = ' 1 day ';
                } else {
                    if ($row['period'] == 'w') {
                        $interval = ' 7 day ';
                    } else {
                        if ($row['period'] == 'b-w') {
                            $interval = ' 14 day ';
                        } else {
                            if ($row['period'] == 'm') {
                                $interval = ' 1 month ';
                            } else {
                                if ($row['period'] == '2m') {
                                    $interval = ' 2 month ';
                                } else {
                                    if ($row['period'] == '3m') {
                                        $interval = ' 3 month ';
                                    } else {
                                        if ($row['period'] == '6m') {
                                            $interval = ' 6 month ';
                                        } else {
                                            if ($row['period'] == 'y') {
                                                $interval = ' 1 year ';
                                            } else {
                                                if ($row['period'] == 'end') {
                                                    $interval = ' '.$row['q_days'].' day ';
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (($percent == 0 and $interval == '')) {
                    continue;
                }

                $dw = -1;
                $q = 'select weekday(\''.$row['last_pay_date'].('\' + interval '.$interval.') as dw');
                ($sth2 = db_query($q));
                while ($row2 = mysql_fetch_array($sth2)) {
                    $dw = $row2['dw'];
                }

                $q = 'select count(*) as col from hm2_history where
                to_days(date) = to_days(\''.$row['last_pay_date'].('\' + interval '.$interval.') and
                deposit_id = ').$row['id'];
                ($sth3 = db_query($q));
                $flag_exists_earning = 0;
                while ($row3 = mysql_fetch_array($sth3)) {
                    $flag_exists_earning = $row3[col];
                }

                if ($flag_exists_earning == 0) {
                    if ((5 <= $dw and $row['work_week'] == 1)) {
                        $q = 'insert into hm2_history set user_id = '.$row['user_id'].',
                    amount = 0,
                    type = \'earning\',
                    description = \'No interest on '.($dw == 5 ? 'Saturday' : 'Sunday').'\',
                    actual_amount = 0,
                    date = \''.$row['last_pay_date'].('\' + interval '.$interval.',
                    ec = ').$row['ec'].',
                    str = \'gg\',
                    deposit_id = '.$row['id'];
                    } else {
                        $q = 'insert into hm2_history set user_id = '.$row['user_id'].(',
                    amount = '.$inc.',
                    type = \'earning\',
                    description = \'Earning from deposit $').number_format($row['actual_amount'], 2).(' - '.$percent.' %\',
                    actual_amount = '.$inc.',
                    date = \'').$row['last_pay_date'].('\' + interval '.$interval.',
                    ec = ').$row['ec'].',
                    str = \'gg\',
                    deposit_id = '.$row['id'];
                    }
                }

                db_query($q);
                $status = '';
                if ($row['period'] == 'end') {
                    if (($row['withdraw_principal'] == 0 or ($row['withdraw_principal'] and $row['wp_ok']))) {
                        $status = ', status = \'off\'';
                    }

                    if (($row['return_profit'] == 1 and ($row['withdraw_principal'] == 0 or ($row['withdraw_principal'] and $row['wp_ok'])))) {
                        $q = 'insert into hm2_history set user_id = '.$row['user_id'].',
                    amount = '.$row['actual_amount'].',
                    type=\'release_deposit\',
                    actual_amount = '.$row['actual_amount'].',
                    ec = '.$row['ec'].',
                    date = \''.$row['last_pay_date'].('\' + interval '.$interval.',
                    deposit_id = ').$row['id'];
                        db_query($q);
                    }
                } else {
                    if ((5 <= $dw and $row['work_week'] == 1)) {
                    } else {
                        if (((0 < $row['compound'] and $row['compound'] <= 100) and $row['use_compound'] == 1)) {
                            if ($row['compound_max_deposit'] == 0) {
                                $row['compound_max_deposit'] = $row['actual_amount'] + 1;
                            }

                            if (($row['compound_min_deposit'] <= $row['actual_amount'] and $row['actual_amount'] <= $row['compound_max_deposit'])) {
                                if ($row['compound_percents_type'] == 1) {
                                    $cps = preg_split('/\\s*,\\s*/', $row['compound_percents']);
                                    if (!in_array($row['compound'], $cps)) {
                                        $row['compound'] = $cps[0];
                                    }
                                } else {
                                    if ($row['compound'] < $row['compound_min_percent']) {
                                        $row['compound'] = $row['compound_min_percent'];
                                    }

                                    if ($row['compound_max_percent'] < $row['compound']) {
                                        $row['compound'] = $row['compound_max_percent'];
                                    }
                                }
                            } else {
                                $row['compound'] = 0;
                            }

                            if ((0 < $row['compound'] and $row['compound'] <= 100)) {
                                $comp_amount = $inc * $row['compound'] / 100;
                                $inc = floor((floor($inc * 100000) / 100000 - floor($comp_amount * 100000) / 100000) * 100) / 100;
                                $q = 'insert into hm2_history set user_id = '.$row['user_id'].(',
                        amount = -'.$comp_amount.',
                    		type=\'deposit\',
                    		description = \'Compounding deposit\',
                    		actual_amount = -'.$comp_amount.',
                    		ec = ').$row['ec'].',
                    		date = \''.$row['last_pay_date'].('\' + interval '.$interval.',
                                deposit_id = ').$row['id'];
                                db_query($q);
                                $q = 'update hm2_deposits set amount = amount + '.$comp_amount.',
                    		actual_amount = actual_amount + '.$comp_amount.'
                    		where id = '.$row['id'];
                                db_query($q);
                            }
                        }

                        pay_direct_earning($row['id'], $inc,
                            'date = \''.$row['last_pay_date'].('\' + interval '.$interval));
                    }
                }

                $q = 'update hm2_deposits set
      	q_pays = q_pays + 1,
      	last_pay_date = last_pay_date + interval '.$interval.' '.$status.' where id ='.$row['id'];
                db_query($q);
            }
        }

        $q = 'select * from hm2_types where q_days > 0';
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            $q_days = $row['q_days'];
            $id = $row['id'];
            if ($row['return_profit'] == 1) {
                $q = 'select * from hm2_deposits where
                type_id = '.$id.' and
                status = \'on\' and
                user_id = '.$row_user_id.' and
                (deposit_date + interval '.$q_days.' day < last_pay_date or deposit_date + interval '.$q_days.' day < now()) and
                (('.$row['withdraw_principal'].' = 0) || ('.$row['withdraw_principal'].' && (deposit_date + interval '.$row['withdraw_principal_duration'].' day < now())))
             ';
                $sth1 = db_query($q);
                while ($row1 = mysql_fetch_array($sth1)) {
                    $q = 'insert into hm2_history set
                user_id = '.$row1['user_id'].',
      		amount = '.$row1['actual_amount'].',
      		type=\'release_deposit\',
      		actual_amount = '.$row1['actual_amount'].',
                      ec = '.$row1['ec'].',
      		date = \''.$row1['deposit_date'].('\' + interval '.$q_days.' day,
                      deposit_id = ').$row1['id'];
                    db_query($q);
                }
            }

            $q = 'update hm2_deposits set status=\'off\' where
             user_id = '.$row_user_id.' and
    	       (deposit_date + interval '.$q_days.' day < last_pay_date or deposit_date + interval '.$q_days.' day < now()) and
             (('.$row['withdraw_principal'].' = 0) || ('.$row['withdraw_principal'].' && (deposit_date + interval '.$row['withdraw_principal_duration'].' day < now()))) and
             type_id = '.$id.'
           ';
            db_query($q);
        }
    }
}

function get_settings()
{
    $s = $_ENV;
    $month = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $s['site_start_month_str_generated'] = $month[$s['site_start_month'] - 1];
    if ($s['show_info_box_running_days'] == 1) {
        $s['site_days_online_generated'] = sprintf('%d',
            (time() - mktime(0, 0, 0, $s['site_start_month'], $s['site_start_day'], $s['site_start_year'])) / 86400);
    }

    $s['time_dif'] = sprintf('%d', $s['time_dif']);
    $s['def_payee_account_wiretransfer'] = (empty($s['enable_wire']) ? 1 : 0);
    $s['def_payee_account_egold'] = $s['def_payee_account'];

    return $s;
}

function save_settings()
{
}

function encode_str($q, $w)
{
    $l = strtoupper(md5($w));
    $j = 0;
    $c = '';
    for ($i = 0; $i < strlen($q); ++$i) {
        if (strlen($l) == $j + 10) {
            $j = 0;
        }

        $c .= sprintf('%02x', ord(substr($q, $i, 1)) ^ ord(substr($l, $j, 1)));
        ++$j;
    }

    return $c;
}

function quote($str)
{
    $str = str_replace('\'', '\'\'', $str);
    $str = str_replace('\\', '\\\\', $str);

    return $str;
}

function gen_confirm_code($len, $md = 1)
{
    $a = [
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        '0',
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z',
    ];
    $i = 0;
    $str = '';
    for ($i = 0; $i < $len; ++$i) {
        $str .= $a[rand(0, count($a) - 1)];
    }

    if ($md) {
        $str = md5($str);
    }

    return $str;
}

function get_rand_md5($len)
{
    $a = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'A', 'B', 'C', 'D', 'E', 'F'];
    $i = 0;
    $str = '';
    for ($i = 0; $i < $len; ++$i) {
        $str .= $a[rand(0, count($a) - 1)];
    }

    return $str;
}

function get_user_balance($id)
{
    $q = 'select type, sum(actual_amount) as sum from hm2_history where user_id = '.$id.' group by type';
    $sth = db_query($q);
    $accounting = [];
    while ($row = mysql_fetch_array($sth)) {
        $accounting[$row['type']] = $row['sum'];
    }

    $total = 0;
    while (list($kk, $vv) = each($accounting)) {
        $total += $vv;
    }

    $accounting['total'] = $total;
    $q = 'select sum(actual_amount) as sum from hm2_deposits where user_id = '.$id.' and status=\'on\'';
    $sth = db_query($q);
    while ($row = mysql_fetch_array($sth)) {
        $accounting['active_deposit'] += $row['sum'];
    }

    return $accounting;
}
