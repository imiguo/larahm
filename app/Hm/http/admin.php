<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Illuminate\Support\Facades\Auth;
$frm['a'] = '';

include app_path('Hm').'/lib/config.inc.php';
require app_path('Hm').'/lib/admin.inc.php';

global $frm;

$userinfo = [];
$userinfo['logged'] = 0;

/*
 * @action showprogramstat
 */
if ($frm['a'] == 'showprogramstat') {
    show_program_stat();
    exit;
}

/*
 * @action logout
 */
if ($frm['a'] == 'logout') {
    Auth::logout();
    return response()->redirectTo('/');
}

try_auth($userinfo);
$username = $userinfo['username'];

/*
 * @action startup_bonus_set
 */
if (($frm['a'] == 'startup_bonus' and $frm['act'] == 'set')) {
    startup_bonus();
    exit;
}

/*
 * @action exchange_rates_save
 */
if (($frm['a'] == 'exchange_rates' and $frm['action'] == 'save')) {
    save_exchange_rates();
    exit;
}

/*
 * @action test_egold_settings
 */
if ($frm['a'] == 'test_egold_settings') {
    include app_path('Hm').'/inc/admin/auto_pay_settings_test.inc.php';
    exit;
}

/*
 * @action test_evocash_settings
 */
if ($frm['a'] == 'test_evocash_settings') {
    include app_path('Hm').'/inc/admin/auto_pay_settings_evocash_test.inc.php';
    exit;
}

/*
 * @action test_intgold_settings
 */
if ($frm['a'] == 'test_intgold_settings') {
    include app_path('Hm').'/inc/admin/auto_pay_settings_intgold_test.inc.php';
    exit;
}

/*
 * @action test_eeecurrency_settings
 */
if ($frm['a'] == 'test_eeecurrency_settings') {
    include app_path('Hm').'/inc/admin/auto_pay_settings_eeecurrency_test.inc.php';
    exit;
}

/*
 * @action test_ebullion_settings
 */
if ($frm['a'] == 'test_ebullion_settings') {
    include app_path('Hm').'/inc/admin/auto_pay_settings_ebullion_test.inc.php';
    exit;
}

if ($userinfo['should_count'] == 1) {
    $q = 'update hm2_users set last_access_time = now() where username=\''.$username.'\'';
    db_query($q);

    count_earning(-1);
}

/*
 * @action affilates_remove_ref
 */
if (($frm['a'] == 'affilates' and $frm['action'] == 'remove_ref')) {
    $u_id = sprintf('%d', $frm['u_id']);
    $ref = sprintf('%d', $frm['ref']);
    $q = 'update hm2_users set ref = 0 where id = '.$ref;
    db_query($q);
    header(''.'Location: ?a=affilates&u_id='.$u_id);
    exit;
}

/*
 * @action affilates_change_upline
 */
if (($frm['a'] == 'affilates' and $frm['action'] == 'change_upline')) {
    $u_id = sprintf('%d', $frm['u_id']);
    $upline = quote($frm['upline']);
    $q = 'select * from hm2_users where username=\''.$upline.'\'';
    $sth = db_query($q);
    $id = 0;
    while ($row = mysql_fetch_array($sth)) {
        $id = $row['id'];
    }

    $q = 'update hm2_users set ref = '.$id.' where id = '.$u_id;
    db_query($q);
    header(''.'Location: ?a=affilates&u_id='.$u_id);
    exit;
}

/*
 * @action pending_deposit_details_movetoproblem
 */
if (($frm['a'] == 'pending_deposit_details' and $frm['action'] == 'movetoproblem')) {
    $id = sprintf('%d', $frm['id']);
    $q = 'update hm2_pending_deposits set status=\'problem\' where id = '.$id;
    db_query($q);
    header('Location: ?a=pending_deposits');
    exit;
}

/*
 * @action pending_deposit_details_movetonew
 */
if (($frm['a'] == 'pending_deposit_details' and $frm['action'] == 'movetonew')) {
    $id = sprintf('%d', $frm['id']);
    $q = 'update hm2_pending_deposits set status=\'new\' where id = '.$id;
    db_query($q);
    header('Location: ?a=pending_deposits&type=problem');
    exit;
}

/*
 * @action pending_deposit_details_delete
 */
if (($frm['a'] == 'pending_deposit_details' and $frm['action'] == 'delete')) {
    $id = sprintf('%d', $frm['id']);
    $q = 'delete from hm2_pending_deposits where id = '.$id;
    db_query($q);
    header('Location: ?a=pending_deposits&type='.$frm['type']);
    exit;
}

/*
 * @action pending_deposit_details_movetodeposit_movetoaccount_yes
 */
if ((($frm['a'] == 'pending_deposit_details' and ($frm['action'] == 'movetodeposit' or $frm['action'] == 'movetoaccount')) and $frm['confirm'] == 'yes')) {
    $deposit_id = $id = sprintf('%d', $frm['id']);
    $q = 'select
          hm2_pending_deposits.*,
          hm2_users.username
        from
          hm2_pending_deposits,
          hm2_users
        where
          hm2_pending_deposits.user_id = hm2_users.id and
          hm2_pending_deposits.id = '.$id.' and
          hm2_pending_deposits.status != \'processed\'
       ';
    $sth = db_query($q);
    $amount = sprintf('%0.2f', $frm['amount']);
    while ($row = mysql_fetch_array($sth)) {
        $ps = $row['ec'];
        $username = $row['username'];
        $compound = sprintf('%d', $row['compound']);
        $fields = $row['fields'];
        $user_id = $row['user_id'];
        if ((100 < $compound or $compound < 0)) {
            $compound = 0;
        }

        $q = 'insert into hm2_history set
            user_id = '.$row['user_id'].(''.',
            date = now(),
            amount = '.$amount.',
            actual_amount = '.$amount.',
            type=\'add_funds\',
            description=\'').quote($exchange_systems[$row['ec']]['name']).' transfer received\',
            ec = '.$row['ec'];
        db_query($q);
        if (($frm['action'] == 'movetodeposit' and 0 < $row[type_id])) {
            $q = 'select name, delay from hm2_types where id = '.$row['type_id'];
            ($sth1 = db_query($q));
            $row1 = mysql_fetch_array($sth1);
            $delay = $row1[delay];
            if (0 < $delay) {
                --$delay;
            }

            $q = 'insert into hm2_deposits set
              user_id = '.$row['user_id'].',
              type_id = '.$row['type_id'].(''.',
              deposit_date = now(),
              last_pay_date = now() + interval '.$delay.' day,
              status = \'on\',
              q_pays = 0,
              amount = '.$amount.',
              actual_amount = '.$amount.',
              ec = '.$ps.',
              compound = '.$compound);
            db_query($q);
            $deposit_id = mysql_insert_id();
            $q = 'insert into hm2_history set
              user_id = '.$row['user_id'].(''.',
              date = now(),
              amount = -'.$amount.',
              actual_amount = -'.$amount.',
              type=\'deposit\',
              description=\'Deposit to ').quote($row1[name]).(''.'\',
              ec = '.$ps.',
              deposit_id = '.$deposit_id.'
           ');
            db_query($q);
            $ref_sum = referral_commission($row['user_id'], $amount, $ps);
        }

        $info = [];
        $q = 'select * from hm2_users where id = '.$user_id;
        $sth1 = db_query($q);
        $userinfo = mysql_fetch_array($sth1);
        $q = 'select * from hm2_types where id = '.$row['type_id'];
        $sth1 = db_query($q);
        $type = mysql_fetch_array($sth1);
        $info['username'] = $userinfo['username'];
        $info['name'] = $userinfo['name'];
        $info['amount'] = number_format($row['amount'], 2);
        $info['currency'] = $exchange_systems[$ps]['name'];
        $info['compound'] = number_format($type['compound'], 2);
        $info['plan'] = (0 < $row[type_id] ? $type['name'] : 'Deposit to Account');
        $q = 'select * from hm2_processings where id = '.$row['ec'];
        $sth = db_query($q);
        $processing = mysql_fetch_array($sth);
        $pfields = unserialize($processing['infofields']);
        $infofields = unserialize($fields);
        $f = '';
        foreach ($pfields as $id => $name) {
            $f .= $name.': '.stripslashes($infofields[$id]).'';
        }

        $info['fields'] = $f;
        $q = 'select date_format(date + interval '.$settings['time_dif'].' hour, \'%b-%e-%Y %r\') as dd from hm2_pending_deposits where id = '.$row['id'];
        ($sth1 = db_query($q));
        $row1 = mysql_fetch_array($sth1);
        $info['deposit_date'] = $row1['dd'];
        $q = 'select email from hm2_users where id = 1';
        $sth1 = db_query($q);
        $admin_row = mysql_fetch_array($sth1);
        send_template_mail('deposit_approved_admin_notification', $admin_row['email'], $settings['opt_in_email'], $info);
        send_template_mail('deposit_approved_user_notification', $userinfo['email'], $settings['opt_in_email'], $info);
    }

    $id = sprintf('%d', $frm['id']);
    $q = 'update hm2_pending_deposits set status=\'processed\' where id = '.$id;
    db_query($q);
    header('Location: ?a=pending_deposits');
    exit;
}

/*
 * @action mass
 */
if ($frm['a'] == 'mass') {
    if ($frm['action2'] == 'massremove') {
        $ids = $frm['pend'];
        reset($ids);
        while (list($kk, $vv) = each($ids)) {
            $q = 'delete from hm2_history where id = '.$kk;
            db_query($q);
        }

        header('Location: ?a=thistory&ttype=withdraw_pending&say=massremove');
        exit;
    }

    if ($frm['action2'] == 'masssetprocessed') {
        $ids = $frm['pend'];
        reset($ids);
        while (list($kk, $vv) = each($ids)) {
            $q = 'select * from hm2_history where id = '.$kk;
            $sth = db_query($q);
            while ($row = mysql_fetch_array($sth)) {
                $q = 'insert into hm2_history set
		user_id = '.$row['user_id'].',
		amount = -'.abs($row['actual_amount']).',
		actual_amount = -'.abs($row['actual_amount']).',
		type = \'withdrawal\',
		date = now(),
		description = \'Withdrawal processed\',
		ec = '.$row['ec'];
                db_query($q);
                $q = 'delete from hm2_history where id = '.$row['id'];
                db_query($q);
                $userinfo = [];
                $q = 'select * from hm2_users where id = '.$row['user_id'];
                $sth1 = db_query($q);
                $userinfo = mysql_fetch_array($sth1);
                $info = [];
                $info['username'] = $userinfo['username'];
                $info['name'] = $userinfo['name'];
                $info['amount'] = number_format(abs($row['amount']), 2);
                $info['currency'] = $exchange_systems[$row['ec']]['name'];
                $info['account'] = 'n/a';
                $info['batch'] = 'n/a';
                $info['paying_batch'] = 'n/a';
                $info['receiving_batch'] = 'n/a';
                send_template_mail('withdraw_user_notification', $userinfo['email'], $settings['opt_in_email'], $info);
                $q = 'select email from hm2_users where id = 1';
                $sth = db_query($q);
                $admin_row = mysql_fetch_array($sth);
                send_template_mail('withdraw_admin_notification', $admin_row['email'], $settings['opt_in_email'], $info);
            }
        }

        header('Location: ?a=thistory&ttype=withdraw_pending&say=massprocessed');
        exit;
    }

    if ($frm['action2'] == 'masscsv') {
        $ids = $frm['pend'];
        if (!$ids) {
            echo 'Nothing selected.';
            exit;
        }

        reset($ids);
        header('Content-type: text/plain');
        $ec = -1;
        $s = '-1';
        while (list($kk, $vv) = each($ids)) {
            $s .= ','.$kk;
        }

        $q = 'select
		h.*,
		u.egold_account,
		u.evocash_account,
		u.intgold_account,
		u.stormpay_account,
		u.ebullion_account,
		u.paypal_account,
		u.goldmoney_account,
		u.eeecurrency_account
              from hm2_history as h, hm2_users as u where h.id in ('.$s.') and u.id = h.user_id order by ec';
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            if (100 < $row['ec']) {
                continue;
            }

            if ($ec != $row['ec']) {
                echo '#'.$exchange_systems[$row['ec']]['name'].' transactions (account, amount)';
                $ec = $row['ec'];
            }

            if ($row['ec'] == 0) {
                $ac = $row['egold_account'];
            } else {
                if ($row['ec'] == 1) {
                    $ac = $row['evocash_account'];
                } else {
                    if ($row['ec'] == 2) {
                        $ac = $row['intgold_account'];
                    } else {
                        if ($row['ec'] == 4) {
                            $ac = $row['stormpay_account'];
                        } else {
                            if ($row['ec'] == 5) {
                                $ac = $row['ebullion_account'];
                            } else {
                                if ($row['ec'] == 6) {
                                    $ac = $row['paypal_account'];
                                } else {
                                    if ($row['ec'] == 7) {
                                        $ac = $row['goldmoney_account'];
                                    } else {
                                        if ($row['ec'] == 8) {
                                            $ac = $row['eeecurrency_account'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $amount = abs($row['amount']);
            $fee = floor($amount * $settings['withdrawal_fee']) / 100;
            if ($fee < $settings['withdrawal_fee_min']) {
                $fee = $settings['withdrawal_fee_min'];
            }

            $to_withdraw = $amount - $fee;
            if ($to_withdraw < 0) {
                $to_withdraw = 0;
            }

            $to_withdraw = sprintf('%.02f', floor($to_withdraw * 100) / 100);
            echo $ac.','.abs($to_withdraw).'';
        }

        exit;
    }

    if (($frm['action2'] == 'masspay' and $frm['action3'] == 'masspay')) {
        if ($settings['demomode'] == 1) {
            exit;
        }

        $ids = $frm['pend'];
        if ($frm['e_acc'] == 1) {
            $egold_account = $frm['egold_account'];
            $egold_password = $frm['egold_password'];
            $settings['egold_from_account'] = $egold_account;
        } else {
            $q = 'select v from hm2_pay_settings where n=\'egold_account_password\'';
            $sth = db_query($q);
            while ($row = mysql_fetch_array($sth)) {
                $egold_account = $settings['egold_from_account'];
                $egold_password = decode_pass_for_mysql($row['v']);
            }
        }

        if ($frm['perfectmoney_acc'] == 1) {
            $egold_account = $frm['perfectmoney_account'];
            $perfectmoney_password = $frm['perfectmoney_password'];
            $settings['perfectmoney_from_account'] = $perfectmoney_account;
        } else {
            $q = 'select v from hm2_pay_settings where n=\'perfectmoney_account_password\'';
            $sth = db_query($q);
            while ($row = mysql_fetch_array($sth)) {
                $perfectmoney_account = $settings['perfectmoney_from_account'];
                $perfectmoney_password = decode_pass_for_mysql($row['v']);
            }
        }

        if ($frm['evo_acc'] == 1) {
            $evocash_account = $frm['evocash_account'];
            $evocash_password = $frm['evocash_password'];
            $evocash_code = $frm['evocash_code'];
            $settings['evocash_username'] = $frm[evocash_name];
            $settings['evocash_from_account'] = $evocash_account;
        } else {
            $q = 'select v from hm2_pay_settings where n=\'evocash_account_password\'';
            $sth = db_query($q);
            while ($row = mysql_fetch_array($sth)) {
                $evocash_account = $settings['evocash_from_account'];
                $evocash_password = decode_pass_for_mysql($row['v']);
            }

            $q = 'select v from hm2_pay_settings where n=\'evocash_transaction_code\'';
            $sth = db_query($q);
            while ($row = mysql_fetch_array($sth)) {
                $evocash_code = decode_pass_for_mysql($row['v']);
            }
        }

        if ($frm['intgold_acc'] == 1) {
            $intgold_account = $frm['intgold_account'];
            $intgold_password = $frm['intgold_password'];
            $intgold_code = $frm['intgold_code'];
            $settings['intgold_from_account'] = $intgold_account;
        } else {
            $q = 'select v from hm2_pay_settings where n=\'intgold_password\'';
            $sth = db_query($q);
            while ($row = mysql_fetch_array($sth)) {
                $intgold_account = $settings['intgold_from_account'];
                $intgold_password = decode_pass_for_mysql($row['v']);
            }

            $q = 'select v from hm2_pay_settings where n=\'intgold_transaction_code\'';
            $sth = db_query($q);
            while ($row = mysql_fetch_array($sth)) {
                $intgold_code = decode_pass_for_mysql($row['v']);
            }
        }

        if ($frm['eeecurrency_acc'] == 1) {
            $eeecurrency_account = $frm['eeecurrency_account'];
            $eeecurrency_password = $frm['eeecurrency_password'];
            $eeecurrency_code = $frm['eeecurrency_code'];
            $settings['eeecurrency_from_account'] = $eeecurrency_account;
        } else {
            $q = 'select v from hm2_pay_settings where n=\'eeecurrency_password\'';
            $sth = db_query($q);
            while ($row = mysql_fetch_array($sth)) {
                $eeecurrency_account = $settings['eeecurrency_from_account'];
                $eeecurrency_password = decode_pass_for_mysql($row['v']);
            }

            $q = 'select v from hm2_pay_settings where n=\'eeecurrency_transaction_code\'';
            $sth = db_query($q);
            while ($row = mysql_fetch_array($sth)) {
                $eeecurrency_code = decode_pass_for_mysql($row['v']);
            }
        }

        @set_time_limit(9999999);
        reset($ids);
        while (list($kk, $vv) = each($ids)) {
            $q = 'select h.*, u.egold_account, u.evocash_account, u.intgold_account, u.ebullion_account, u.eeecurrency_account, u.username, u.name, u.email from hm2_history as h, hm2_users as u where h.id = '.$kk.' and u.id = h.user_id and h.ec in (0, 1, 2, 5, 8, 9)';
            $sth = db_query($q);
            while ($row = mysql_fetch_array($sth)) {
                $amount = abs($row['actual_amount']);
                $fee = floor($amount * $settings['withdrawal_fee']) / 100;
                if ($fee < $settings['withdrawal_fee_min']) {
                    $fee = $settings['withdrawal_fee_min'];
                }

                $to_withdraw = $amount - $fee;
                if ($to_withdraw < 0) {
                    $to_withdraw = 0;
                }

                $to_withdraw = sprintf('%.02f', floor($to_withdraw * 100) / 100);
                $success_txt = 'Withdrawal to '.$row['username'].' from '.$settings['site_name'];
                if ($row['ec'] == 0) {
                    $error_txt = 'Error, tried to send '.$to_withdraw.' to e-gold account # '.$row['egold_account'].'. Error:';
                    list($res, $text, $batch) = send_money_to_egold($egold_password, $to_withdraw,
                        $row['egold_account'], $success_txt, $error_txt);
                }

                if ($row['ec'] == 1) {
                    $error_txt = 'Error, tried to send '.$to_withdraw.' to evocash account # '.$row['evocash_account'].'. Error:';
                    list($res, $text, $batch) = send_money_to_evocash(''.$evocash_password.'|'.$evocash_code,
                        $to_withdraw, $row['evocash_account'], $success_txt, $error_txt);
                }

                if ($row['ec'] == 2) {
                    $error_txt = 'Error, tried to send '.$to_withdraw.' to IntGold account # '.$row['intgold_account'].'. Error:';
                    list($res, $text, $batch) = send_money_to_intgold(''.$intgold_password.'|'.$intgold_code,
                        $to_withdraw, $row['intgold_account'], $success_txt, $error_txt);
                }

                if ($row['ec'] == 3) {
                    $error_txt = 'Error, tried to send '.$to_withdraw.' to Perfect Money account # '.$row['perfectmoney_account'].'. Error:';
                    list($res, $text, $batch) = send_money_to_perfectmoney(''.$perfectmoney_password.'|'.$perfectmoney_code,
                        $to_withdraw, $row['perfectmoney_account'], $success_txt, $error_txt);
                }

                if ($row['ec'] == 5) {
                    $error_txt = 'Error, tried to send '.$to_withdraw.' to e-Bullion account # '.$row['ebullion_account'].'. Error:';
                    list($res, $text, $batch) = send_money_to_ebullion('', $to_withdraw, $row['ebullion_account'],
                        $success_txt, $error_txt);
                }

                if ($row['ec'] == 8) {
                    $error_txt = 'Error, tried to send '.$to_withdraw.' to eeeCurrency account # '.$row['eeecurrency_account'].'. Error:';
                    list($res, $text, $batch) = send_money_to_eeecurrency(''.$eeecurrency_password.'|'.$eeecurrency_code,
                        $to_withdraw, $row['eeecurrency_account'], $success_txt, $error_txt);
                }

                if ($res == 1) {
                    $q = 'delete from hm2_history where id = '.$kk;
                    db_query($q);
                    $d_account = [
                        $row[egold_account],
                        $row[evocash_account],
                        $row[intgold_account],
                        '',
                        $row[stormpay_account],
                        $row[ebullion_account],
                        $row[paypal_account],
                        $row[goldmoney_account],
                        $row[eeecurrency_account],
                    ];
                    $q = 'insert into hm2_history set
              user_id = '.$row['user_id'].(''.',
              amount = -'.$amount.',
              actual_amount = -'.$amount.',
              type=\'withdrawal\',
              date = now(),
              ec = ').$row['ec'].',
              description = \'Withdrawal to account '.$d_account[$row[ec]].(''.'. Batch is '.$batch.'\'');
                    db_query($q);
                    $info = [];
                    $info['username'] = $row['username'];
                    $info['name'] = $row['name'];
                    $info['amount'] = sprintf('%.02f', 0 - $row['amount']);
                    $info['account'] = $d_account[$row[ec]];
                    $info['batch'] = $batch;
                    $info['currency'] = $exchange_systems[$row['ec']]['name'];
                    send_template_mail('withdraw_user_notification', $row['email'], $settings['system_email'], $info);
                    echo 'Sent $ '.$to_withdraw.' to account'.$d_account[$row[ec]].' on '.$exchange_systems[$row['ec']]['name'].(''.'. Batch is '.$batch.'<br>');
                } else {
                    echo $text.'<br>';
                }

                flush();
            }
        }

        exit;
    }
}

/*
 * @action auto-pay-settings
 */
if (($frm['a'] == 'auto-pay-settings' and $frm['action'] == 'auto-pay-settings')) {
    if ($settings['demomode'] != 1) {
        if (($userinfo['transaction_code'] != '' and $userinfo['transaction_code'] != $frm['alternative_passphrase'])) {
            header('Location: ?a=auto-pay-settings&say=invalid_passphrase');
            exit;
        }

        $settings['use_auto_payment'] = $frm['use_auto_payment'];
        $settings['egold_from_account'] = $frm['egold_from_account'];
        $settings['evocash_from_account'] = $frm['evocash_from_account'];
        $settings['evocash_username'] = $frm['evocash_username'];
        if ($frm['evocash_account_password'] != '') {
            $evo_pass = quote(encode_pass_for_mysql($frm['evocash_account_password']));
            $q = 'delete from hm2_pay_settings where n=\'evocash_account_password\'';
            db_query($q);
            $q = 'insert into hm2_pay_settings set n=\'evocash_account_password\', v=\''.$evo_pass.'\'';
            db_query($q);
        }

        if ($frm['evocash_transaction_code'] != '') {
            $evo_code = quote(encode_pass_for_mysql($frm['evocash_transaction_code']));
            $q = 'delete from hm2_pay_settings where n=\'evocash_transaction_code\'';
            db_query($q);
            $q = 'insert into hm2_pay_settings set n=\'evocash_transaction_code\', v=\''.$evo_code.'\'';
            db_query($q);
        }

        $settings['intgold_from_account'] = $frm['intgold_from_account'];
        if ($frm['intgold_password'] != '') {
            $intgold_pass = quote(encode_pass_for_mysql($frm['intgold_password']));
            $q = 'delete from hm2_pay_settings where n=\'intgold_password\'';
            db_query($q);
            $q = 'insert into hm2_pay_settings set n=\'intgold_password\', v=\''.$intgold_pass.'\'';
            db_query($q);
        }

        if ($frm['intgold_transaction_code'] != '') {
            $intgold_code = quote(encode_pass_for_mysql($frm['intgold_transaction_code']));
            $q = 'delete from hm2_pay_settings where n=\'intgold_transaction_code\'';
            db_query($q);
            $q = 'insert into hm2_pay_settings set n=\'intgold_transaction_code\', v=\''.$intgold_code.'\'';
            db_query($q);
        }

        $settings['eeecurrency_from_account'] = $frm['eeecurrency_from_account'];
        if ($frm['eeecurrency_password'] != '') {
            $eeecurrency_pass = quote(encode_pass_for_mysql($frm['eeecurrency_password']));
            $q = 'delete from hm2_pay_settings where n=\'eeecurrency_password\'';
            db_query($q);
            $q = 'insert into hm2_pay_settings set n=\'eeecurrency_password\', v=\''.$eeecurrency_pass.'\'';
            db_query($q);
        }

        if ($frm['eeecurrency_transaction_code'] != '') {
            $eeecurrency_code = quote(encode_pass_for_mysql($frm['eeecurrency_transaction_code']));
            $q = 'delete from hm2_pay_settings where n=\'eeecurrency_transaction_code\'';
            db_query($q);
            $q = 'insert into hm2_pay_settings set n=\'eeecurrency_transaction_code\', v=\''.$eeecurrency_code.'\'';
            db_query($q);
        }

        $settings['min_auto_withdraw'] = $frm['min_auto_withdraw'];
        $settings['max_auto_withdraw'] = $frm['max_auto_withdraw'];
        $settings['max_auto_withdraw_user'] = $frm['max_auto_withdraw_user'];
        save_settings();
        if ($frm['egold_account_password'] != '') {
            $e_pass = quote(encode_pass_for_mysql($frm['egold_account_password']));
            $q = 'delete from hm2_pay_settings where n=\'egold_account_password\'';
            db_query($q);
            $q = 'insert into hm2_pay_settings set n=\'egold_account_password\', v=\''.$e_pass.'\'';
            db_query($q);
        }
    }

    header('Location: ?a=auto-pay-settings&say=done');
    exit;
}

/*
 * @action referal_change
 */
if (($frm['a'] == 'referal' and $frm['action'] == 'change')) {
    if ($settings['demomode'] == 1) {
    } else {
        $q = 'delete from hm2_referal where level = 1';
        db_query($q);
        for ($i = 0; $i < 300; ++$i) {
            if ($frm['active'][$i] == 1) {
                $qname = quote($frm['ref_name'][$i]);
                $from = sprintf('%d', $frm['ref_from'][$i]);
                $to = sprintf('%d', $frm['ref_to'][$i]);
                $percent = sprintf('%0.2f', $frm['ref_percent'][$i]);
                $percent_daily = sprintf('%0.2f', $frm['ref_percent_daily'][$i]);
                $percent_weekly = sprintf('%0.2f', $frm['ref_percent_weekly'][$i]);
                $percent_monthly = sprintf('%0.2f', $frm['ref_percent_monthly'][$i]);
                $q = 'insert into hm2_referal set
  	level = 1,
  	name= \''.$qname.'\',
  	from_value = '.$from.',
  	to_value= '.$to.',
  	percent = '.$percent.',
  	percent_daily = '.$percent_daily.',
  	percent_weekly = '.$percent_weekly.',
  	percent_monthly = '.$percent_monthly;
                db_query($q);
                continue;
            }
        }

        $settings['use_referal_program'] = sprintf('%d', $frm['usereferal']);
        $settings['force_upline'] = sprintf('%d', $frm['force_upline']);
        $settings['get_rand_ref'] = sprintf('%d', $frm['get_rand_ref']);
        $settings['use_active_referal'] = sprintf('%d', $frm['useactivereferal']);
        $settings['pay_active_referal'] = sprintf('%d', $frm['payactivereferal']);
        $settings['use_solid_referral_commission'] = sprintf('%d', $frm['use_solid_referral_commission']);
        $settings['solid_referral_commission_amount'] = sprintf('%.02f', $frm['solid_referral_commission_amount']);
        $settings['ref2_cms'] = sprintf('%0.2f', $frm['ref2_cms']);
        $settings['ref3_cms'] = sprintf('%0.2f', $frm['ref3_cms']);
        $settings['ref4_cms'] = sprintf('%0.2f', $frm['ref4_cms']);
        $settings['ref5_cms'] = sprintf('%0.2f', $frm['ref5_cms']);
        $settings['ref6_cms'] = sprintf('%0.2f', $frm['ref6_cms']);
        $settings['ref7_cms'] = sprintf('%0.2f', $frm['ref7_cms']);
        $settings['ref8_cms'] = sprintf('%0.2f', $frm['ref8_cms']);
        $settings['ref9_cms'] = sprintf('%0.2f', $frm['ref9_cms']);
        $settings['ref10_cms'] = sprintf('%0.2f', $frm['ref10_cms']);
        $settings['show_referals'] = sprintf('%d', $frm['show_referals']);
        $settings['show_refstat'] = sprintf('%d', $frm['show_refstat']);
        save_settings();
    }

    header('Location: ?a=referal');
    exit;
}

/*
 * @action deleterate
 */
if ($frm['a'] == 'deleterate') {
    $id = sprintf('%d', $frm['id']);
    if (($id < 3 and $settings['demomode'] == 1)) {
    } else {
        $q = 'delete from hm2_types where id = '.$id;
        db_query($q);
        $q = 'delete from hm2_plans where parent = '.$id;
        db_query($q);
    }

    header('Location: ?a=rates');
    exit;
}

/*
 * @action newsletter_newsletter
 */
if (($frm['a'] == 'newsletter' and $frm['action'] == 'newsletter')) {
    if ($frm['to'] == 'user') {
        $q = 'select * from hm2_users where username = \''.quote($frm['username']).'\'';
    } else {
        if ($frm['to'] == 'all') {
            $q = 'select * from hm2_users where id > 1';
        } else {
            if ($frm['to'] == 'active') {
                $q = 'select hm2_users.* from hm2_users, hm2_deposits where hm2_users.id > 1 and hm2_deposits.user_id = hm2_users.id group by hm2_users.id';
            } else {
                if ($frm['to'] == 'passive') {
                    $q = 'select u.* from hm2_users as u left outer join hm2_deposits as d on u.id = d.user_id where u.id > 1 and d.user_id is NULL';
                } else {
                    header('Location: ?a=newsletter&say=someerror');
                    exit;
                }
            }
        }
    }

    $sth = db_query($q);
    $flag = 0;
    $total = 0;
    echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>HYIP Manager Pro. Auto-payment, mass payment included.</title>
<link href="images/adminstyle.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="#FFFFF2" link="#666699" vlink="#666699" alink="#666699" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" >
<center>';
    echo '<br><br><br><br><br><div id=\'newsletterplace\'></div>';
    echo '<div id=self_menu0></div>';
    $description = $frm['description'];
    if ($settings['demomode'] != 1) {
        set_time_limit(9999999);
        while ($row = mysql_fetch_array($sth)) {
            $flag = 1;
            ++$total;
            $mailcont = $description;
            $mailcont = ereg_replace('#username#', $row['username'], $mailcont);
            $mailcont = ereg_replace('#name#', $row['name'], $mailcont);
            $mailcont = ereg_replace('#date_register#', $row['date_register'], $mailcont);
            $mailcont = ereg_replace('#egold_account#', $row['egold_account'], $mailcont);
            $mailcont = ereg_replace('#email#', $row['email'], $mailcont);
            send_mail($row['email'], $frm['subject'], $mailcont, 'From: '.$settings['system_email'].'
Reply-To: '.$settings['system_email']);
            echo '<script>var obj = document.getElementById(\'newsletterplace\');
var menulast = document.getElementById(\'self_menu'.($total - 1).'\');
menulast.style.display=\'none\';</script>';
            echo '<div id=\'self_menu'.$total.'\'>Just sent to '.$row[email].(''.'<br>Total '.$total.' messages sent.</div>');
            echo '<script>var menu = document.getElementById(\'self_menu'.$total.'\');
obj.appendChild(menu);
</script>';
            flush();
        }
    }

    if ($flag == 1) {
    }

    echo '<br><br><br>Sent '.$total.'.</center></body></html>';
    exit;
}

/*
 * @action edit_emails_update_statuses
 */
if (($frm['a'] == 'edit_emails' and $frm['action'] == 'update_statuses')) {
    $q = 'update hm2_emails set status = 0';
    db_query($q);
    $update_emails = $frm['emails'];
    if (is_array($update_emails)) {
        foreach ($update_emails as $email_id => $tmp) {
            $q = 'update hm2_emails set status = 1 where id = \''.$email_id.'\'';
            db_query($q);
        }
    }

    header('Location: ?a=edit_emails');
    exit;
}

/*
 * @action send_bonuce_send_bonuce_confirm
 */
if (($frm['a'] == 'send_bonuce' and ($frm['action'] == 'send_bonuce' or $frm['action'] == 'confirm'))) {
    $amount = sprintf('%0.2f', $frm['amount']);
    if ($amount == 0) {
        header('Location: ?a=send_bonuce&say=wrongamount');
        exit;
    }

    $deposit = intval($frm['deposit']);
    $hyip_id = intval($frm['hyip_id']);
    if ($deposit == 1) {
        $q = 'select * from hm2_types where id = '.$hyip_id.' and status = \'on\'';
        $sth = db_query($q);
        $type = mysql_fetch_array($sth);
        if (!$type) {
            header('Location: ?a=send_bonuce&say=wrongplan');
            exit;
        }
    }

    $ec = sprintf('%d', $frm['ec']);
    if ($frm['to'] == 'user') {
        $q = 'select * from hm2_users where username = \''.quote($frm['username']).'\'';
    } else {
        if ($frm['to'] == 'all') {
            $q = 'select * from hm2_users where id > 1';
        } else {
            if ($frm['to'] == 'active') {
                $q = 'select hm2_users.* from hm2_users, hm2_deposits where hm2_users.id > 1 and hm2_deposits.user_id = hm2_users.id group by hm2_users.id';
            } else {
                if ($frm['to'] == 'passive') {
                    $q = 'select u.* from hm2_users as u left outer join hm2_deposits as d on u.id = d.user_id where u.id > 1 and d.user_id is NULL';
                } else {
                    header('Location: ?a=send_bonuce&say=someerror');
                    exit;
                }
            }
        }
    }

    session_start();
    if ($frm['action'] == 'send_bonuce') {
        $code = substr($_SESSION['code'], 23, -32);
        if ($code === md5($frm['code'])) {
            $sth = db_query($q);
            $flag = 0;
            $total = 0;
            $description = quote($frm['description']);
            while ($row = mysql_fetch_array($sth)) {
                $flag = 1;
                $total += $amount;
                $q = 'insert into hm2_history set
    	user_id = '.$row['id'].(''.',
    	amount = '.$amount.',
    	description = \''.$description.'\',
    	type=\'bonus\',
    	actual_amount = '.$amount.',
    	ec = '.$ec.',
    	date = now()');
                db_query($q);
                if ($deposit) {
                    $delay = $type['delay'] - 1;
                    if ($delay < 0) {
                        $delay = 0;
                    }

                    $user_id = $row['id'];
                    $q = 'insert into hm2_deposits set
               user_id = '.$user_id.',
               type_id = '.$hyip_id.',
               deposit_date = now(),
               last_pay_date = now()+ interval '.$delay.' day,
               status = \'on\',
               q_pays = 0,
               amount = \''.$amount.'\',
               actual_amount = \''.$amount.'\',
               ec = '.$ec.'
               ';
                    db_query($q);
                    $deposit_id = mysql_insert_id();
                    $q = 'insert into hm2_history set
               user_id = '.$user_id.',
               amount = \'-'.$amount.'\',
               type = \'deposit\',
               description = \'Deposit to '.quote($type['name']).(''.'\',
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
                            continue;
                        }

                        continue;
                    }

                    continue;
                }
            }

            if ($flag == 1) {
                header(''.'Location: ?a=send_bonuce&say=send&total='.$total);
            } else {
                header('Location: ?a=send_bonuce&say=notsend');
            }

            $_SESSION['code'] = '';
            exit;
        } else {
            header('Location: ?a=send_bonuce&say=invalid_code');
            exit;
        }
    }

    $code = '';
    if ($frm['action'] == 'confirm') {
        $account = preg_split('/,/', $frm['conf_email']);
        $conf_email = array_pop($account);
        $frm_env['HTTP_HOST'] = preg_replace('/www\\./', '', $frm_env['HTTP_HOST']);
        $conf_email .= '@'.$frm_env['HTTP_HOST'];
        $code = get_rand_md5(8);
        send_mail($conf_email, 'Bonus Confirmation Code', 'Code is: '.$code, 'From: '.$settings['system_email'].'
Reply-To: '.$settings['system_email']);
        $code = get_rand_md5(23).md5($code).get_rand_md5(32);
        $_SESSION['code'] = $code;
    }
}

/*
 * @action send_penality_send_penality
 */
if (($frm['a'] == 'send_penality' and $frm['action'] == 'send_penality')) {
    $amount = sprintf('%0.2f', abs($frm['amount']));
    if ($amount == 0) {
        header('Location: ?a=send_penality&say=wrongamount');
        exit;
    }

    $ec = sprintf('%d', $frm['ec']);
    if ($frm['to'] == 'user') {
        $q = 'select * from hm2_users where username = \''.quote($frm['username']).'\'';
    } else {
        if ($frm['to'] == 'all') {
            $q = 'select * from hm2_users where id > 1';
        } else {
            if ($frm['to'] == 'active') {
                $q = 'select hm2_users.* from hm2_users, hm2_deposits where hm2_users.id > 1 and hm2_deposits.user_id = hm2_users.id group by hm2_users.id';
            } else {
                if ($frm['to'] == 'passive') {
                    $q = 'select u.* from hm2_users as u left outer join hm2_deposits as d on u.id = d.user_id where u.user_id > 1 and d.user_id is NULL';
                } else {
                    header('Location: ?a=send_penality&say=someerror');
                    exit;
                }
            }
        }
    }

    $sth = db_query($q);
    $flag = 0;
    $total = 0;
    $description = quote($frm['description']);
    while ($row = mysql_fetch_array($sth)) {
        $flag = 1;
        $total += $amount;
        $q = 'insert into hm2_history set
	user_id = '.$row['id'].(''.',
	amount = -'.$amount.',
	description = \''.$description.'\',
	type=\'penality\',
	actual_amount = -'.$amount.',
	ec = '.$ec.',
	date = now()');
        db_query($q);
    }

    if ($flag == 1) {
        header(''.'Location: ?a=send_penality&say=send&total='.$total);
    } else {
        header('Location: ?a=send_penality&say=notsend');
    }

    exit;
}

/*
 * @action info_box
 */
if (($frm['a'] == 'info_box' and $frm['action'] == 'info_box')) {
    if ($settings['demomode'] != 1) {
        $settings['show_info_box'] = sprintf('%d', $frm['show_info_box']);
        $settings['show_info_box_started'] = sprintf('%d', $frm['show_info_box_started']);
        $settings['show_info_box_running_days'] = sprintf('%d', $frm['show_info_box_running_days']);
        $settings['show_info_box_total_accounts'] = sprintf('%d', $frm['show_info_box_total_accounts']);
        $settings['show_info_box_active_accounts'] = sprintf('%d', $frm['show_info_box_active_accounts']);
        $settings['show_info_box_vip_accounts'] = sprintf('%d', $frm['show_info_box_vip_accounts']);
        $settings['vip_users_deposit_amount'] = sprintf('%d', $frm['vip_users_deposit_amount']);
        $settings['show_info_box_deposit_funds'] = sprintf('%d', $frm['show_info_box_deposit_funds']);
        $settings['show_info_box_today_deposit_funds'] = sprintf('%d', $frm['show_info_box_today_deposit_funds']);
        $settings['show_info_box_total_withdraw'] = sprintf('%d', $frm['show_info_box_total_withdraw']);
        $settings['show_info_box_visitor_online'] = sprintf('%d', $frm['show_info_box_visitor_online']);
        $settings['show_info_box_members_online'] = sprintf('%d', $frm['show_info_box_members_online']);
        $settings['show_info_box_newest_member'] = sprintf('%d', $frm['show_info_box_newest_member']);
        $settings['show_info_box_last_update'] = sprintf('%d', $frm['show_info_box_last_update']);
        $settings['show_kitco_dollar_per_ounce_box'] = sprintf('%d', $frm['show_kitco_dollar_per_ounce_box']);
        $settings['show_kitco_euro_per_ounce_box'] = sprintf('%d', $frm['show_kitco_euro_per_ounce_box']);
        $settings['show_stats_box'] = sprintf('%d', $frm['show_stats_box']);
        $settings['show_members_stats'] = sprintf('%d', $frm['show_members_stats']);
        $settings['show_paidout_stats'] = sprintf('%d', $frm['show_paidout_stats']);
        $settings['show_top10_stats'] = sprintf('%d', $frm['show_top10_stats']);
        $settings['show_last10_stats'] = sprintf('%d', $frm['show_last10_stats']);
        $settings['show_refs10_stats'] = sprintf('%d', $frm['show_refs10_stats']);
        $settings['refs10_start_date'] = sprintf('%04d-%02d-%02d', substr($frm['refs10_start_date'], 0, 4),
            substr($frm['refs10_start_date'], 5, 2), substr($frm['refs10_start_date'], 8, 2));
        $settings['show_news_box'] = sprintf('%d', $frm['show_news_box']);
        $settings['last_news_count'] = sprintf('%d', $frm['last_news_count']);
        save_settings();
    }
}

/*
 * @action settings
 */
if (($frm['a'] == 'settings' and $frm['action'] == 'settings')) {
    if ($settings['demomode'] == 1) {
    } else {
        if (($userinfo['transaction_code'] != '' and $userinfo['transaction_code'] != $frm['alternative_passphrase'])) {
            header('Location: ?a=settings&say=invalid_passphrase');
            exit;
        }

        if ($frm['admin_stat_password'] == '') {
            $q = 'update hm2_users set stat_password = \'\' where id = 1';
            db_query($q);
        } else {
            if ($frm['admin_stat_password'] != '*****') {
                $sp = md5($frm['admin_stat_password']);
                $q = 'update hm2_users set stat_password = \''.$sp.'\' where id = 1';
                db_query($q);
            }
        }

        $settings['site_name'] = $frm['site_name'];
        $settings['reverse_columns'] = sprintf('%d', $frm['reverse_columns']);
        $settings['site_start_day'] = $frm['site_start_day'];
        $settings['site_start_month'] = $frm['site_start_month'];
        $settings['site_start_year'] = $frm['site_start_year'];
        $settings['deny_registration'] = ($frm['deny_registration'] ? 1 : 0);

        $settings['def_payee_account_perfectmoney'] = $frm['def_payee_account_perfectmoney'];
        $settings['def_payee_name_perfectmoney'] = $frm['def_payee_name_perfectmoney'];
        $settings['md5altphrase_perfectmoney'] = $frm['md5altphrase_perfectmoney'];

        $settings['def_payee_account_payeer'] = $frm['def_payee_account_payeer'];
        $settings['def_payee_key_payeer'] = $frm['def_payee_key_payeer'];
        $settings['def_payee_additionalkey_payeer'] = $frm['def_payee_additionalkey_payeer'];

        $settings['def_payee_account_bitcoin'] = $frm['def_payee_account_bitcoin'];
        $settings['def_payee_qrcode_bitcoin'] = $frm['def_payee_qrcode_bitcoin'];

        $settings['def_payee_account'] = $frm['def_payee_account'];
        $settings['def_payee_name'] = $frm['def_payee_name'];
        $settings['md5altphrase'] = $frm['md5altphrase'];

        $settings['def_payee_account_evocash'] = $frm['def_payee_account_evocash'];
        $settings['md5altphrase_evocash'] = $frm['md5altphrase_evocash'];

        $settings['def_payee_account_intgold'] = $frm['def_payee_account_intgold'];
        $settings['md5altphrase_intgold'] = $frm['md5altphrase_intgold'];
        $settings['intgold_posturl'] = sprintf('%d', $frm['intgold_posturl']);

        $settings['use_opt_in'] = sprintf('%d', $frm['use_opt_in']);
        $settings['opt_in_email'] = $frm['opt_in_email'];
        $settings['system_email'] = $frm['system_email'];

        $settings['usercanchangeegoldacc'] = sprintf('%d', $frm['usercanchangeegoldacc']);
        $settings['usercanchangeperfectmoneyacc'] = sprintf('%d', $frm['usercanchangeperfectmoneyacc']);
        $settings['usercanchangeemail'] = sprintf('%d', $frm['usercanchangeemail']);

        $settings['sendnotify_when_userinfo_changed'] = sprintf('%d', $frm['sendnotify_when_userinfo_changed']);
        $settings['graph_validation'] = sprintf('%d', $frm['graph_validation']);
        $settings['graph_max_chars'] = $frm['graph_max_chars'];
        $settings['graph_text_color'] = $frm['graph_text_color'];
        $settings['graph_bg_color'] = $frm['graph_bg_color'];
        $settings['use_number_validation_number'] = sprintf('%d', $frm['use_number_validation_number']);
        $settings['advanced_graph_validation'] = ($frm['advanced_graph_validation'] ? 1 : 0);
        if (!function_exists('imagettfbbox')) {
            $settings['advanced_graph_validation'] = 0;
        }

        $settings['advanced_graph_validation_min_font_size'] = sprintf('%d',
            $frm['advanced_graph_validation_min_font_size']);
        $settings['advanced_graph_validation_max_font_size'] = sprintf('%d',
            $frm['advanced_graph_validation_max_font_size']);
        $settings['enable_calculator'] = $frm['enable_calculator'];
        $settings['accesswap'] = sprintf('%d', $frm['usercanaccesswap']);
        $settings['time_dif'] = $frm['time_dif'];
        $settings['internal_transfer_enabled'] = ($frm['internal_transfer_enabled'] ? 1 : 0);

        $settings['def_payee_account_stormpay'] = $frm['def_payee_account_stormpay'];
        $settings['md5altphrase_stormpay'] = $frm['md5altphrase_stormpay'];
        $settings['stormpay_posturl'] = $frm['stormpay_posturl'];
        $settings['dec_stormpay_fee'] = sprintf('%d', $frm['dec_stormpay_fee']);

        $settings['def_payee_account_paypal'] = $frm['def_payee_account_paypal'];

        $settings['def_payee_account_goldmoney'] = $frm['def_payee_account_goldmoney'];
        $settings['md5altphrase_goldmoney'] = $frm['md5altphrase_goldmoney'];

        $settings['def_payee_account_eeecurrency'] = $frm['def_payee_account_eeecurrency'];
        $settings['md5altphrase_eeecurrency'] = $frm['md5altphrase_eeecurrency'];
        $settings['eeecurrency_posturl'] = sprintf('%d', $frm['eeecurrency_posturl']);

        $settings['gpg_path'] = $frm['gpg_path'];
        $atip_pl = $_FILES['atip_pl'];
        if ((0 < $atip_pl['size'] and $atip_pl['error'] == 0)) {
            $fp = fopen($atip_pl['tmp_name'], 'r');
            while (!feof($fp)) {
                $buf = fgets($fp, 4096);
                if (preg_match('/my\\s+\\(\\$account\\)\\s+\\=\\s+\'([^\']+)\'/', $buf, $matches)) {
                    $frm['def_payee_account_ebullion'] = $matches[1];
                }

                if (preg_match('/my\\s+\\(\\$passphrase\\)\\s+\\=\\s+\'([^\']+)\'/', $buf, $matches)) {
                    $frm['md5altphrase_ebullion'] = $matches[1];
                    continue;
                }
            }

            fclose($fp);
            unlink($atip_pl['tmp_name']);
        }

        $status_php = $_FILES['status_php'];
        if ((0 < $status_php['size'] and $status_php['error'] == 0)) {
            $fp = fopen($status_php['tmp_name'], 'r');
            while (!feof($fp)) {
                $buf = fgets($fp, 4096);
                if (preg_match('/\\$eb_keyID\\s+\\=\\s+\'([^\']+)\'/', $buf, $matches)) {
                    $frm['ebullion_keyID'] = $matches[1];
                    continue;
                }
            }

            fclose($fp);
            unlink($status_php['tmp_name']);
        }

        $pubring_gpg = $_FILES['pubring_gpg'];
        if ((0 < $pubring_gpg['size'] and $pubring_gpg['error'] == 0)) {
            copy($pubring_gpg['tmp_name'], storage_path('tmpl_c').'/pubring.gpg');
            unlink($pubring_gpg['tmp_name']);
        }

        $secring_gpg = $_FILES['secring_gpg'];
        if ((0 < $secring_gpg['size'] and $secring_gpg['error'] == 0)) {
            copy($secring_gpg['tmp_name'], storage_path('tmpl_c').'/secring.gpg');
            unlink($secring_gpg['tmp_name']);
        }

        $settings['def_payee_account_ebullion'] = $frm['def_payee_account_ebullion'];
        $settings['def_payee_name_ebullion'] = $frm['def_payee_name_ebullion'];
        $settings['md5altphrase_ebullion'] = encode_pass_for_mysql($frm['md5altphrase_ebullion']);
        $settings['ebullion_keyID'] = $frm['ebullion_keyID'];
        $settings['brute_force_handler'] = ($frm['brute_force_handler'] ? 1 : 0);
        $settings['brute_force_max_tries'] = sprintf('%d', abs($frm['brute_force_max_tries']));
        $settings['redirect_to_https'] = ($frm['redirect_to_https'] ? 1 : 0);
        $settings['use_user_location'] = ($frm['use_user_location'] ? 1 : 0);
        $settings['use_transaction_code'] = ($frm['use_transaction_code'] ? 1 : 0);
        $settings['min_user_password_length'] = sprintf('%d', $frm['min_user_password_length']);
        $settings['use_history_balance_mode'] = ($frm['use_history_balance_mode'] ? 1 : 0);
        $settings['account_update_confirmation'] = ($frm['account_update_confirmation'] ? 1 : 0);
        $settings['withdrawal_fee'] = sprintf('%.02f', $frm['withdrawal_fee']);
        if ($settings['withdrawal_fee'] < 0) {
            $settings['withdrawal_fee'] = '0.00';
        }

        if (100 < $settings['withdrawal_fee']) {
            $settings['withdrawal_fee'] = '100.00';
        }

        $settings['withdrawal_fee_min'] = sprintf('%.02f', $frm['withdrawal_fee_min']);
        $settings['min_withdrawal_amount'] = sprintf('%.02f', $frm['min_withdrawal_amount']);
        $settings[max_daily_withdraw] = sprintf('%0.2f', $frm[max_daily_withdraw]);
        $settings['use_add_funds'] = ($frm['use_add_funds'] ? 1 : 0);
        $login = quote($frm['admin_login']);
        $pass = quote($frm['admin_password']);
        $email = quote($frm['admin_email']);

        if (($login != '' and $email != '')) {
            $q = 'update hm2_users set email=\''.$email.'\', username=\''.$login.'\' where id = 1';
            db_query($q);
        }

        if ($pass != '') {
            $md_pass = md5($pass);
            $q = 'update hm2_users set password = \''.$md_pass.'\' where id = 1';
            db_query($q);
        }

        if (($frm['use_alternative_passphrase'] == 1 and $frm['new_alternative_passphrase'] != '')) {
            $altpass = quote($frm['new_alternative_passphrase']);
            $q = 'update hm2_users set transaction_code = \''.$altpass.'\' where id = 1';
            db_query($q);
        }

        if ($frm['use_alternative_passphrase'] == 0) {
            $q = 'update hm2_users set transaction_code = \'\' where id = 1';
            db_query($q);
        }

        save_settings();
    }

    header('Location: ?a=settings&say=done');
    exit;
}

/*
 * @action rm_withdraw
 */
if ($frm['a'] == 'rm_withdraw') {
    $id = sprintf('%d', $frm['id']);
    $q = 'delete from hm2_history where id = '.$id;
    db_query($q);
    header('Location: ?a=thistory&ttype=withdraw_pending');
    exit;
}

/*
 * @action releasedeposits
 */
if (($frm['a'] == 'releasedeposits' and $frm['action'] == 'releasedeposits')) {
    $u_id = sprintf('%d', $frm['u_id']);
    $type_ids = $frm['type_id'];
    while (list($kk, $vv) = each($type_ids)) {
        $kk = intval($kk);
        $vv = intval($vv);
        $q = 'select compound, actual_amount from hm2_deposits where id = '.$kk;
        $sth = db_query($q);
        $row = mysql_fetch_array($sth);
        $compound = $row['compound'];
        $amount = $row['actual_amount'];
        $q = 'select * from hm2_types where id = '.$vv;
        $sth = db_query($q);
        $type = mysql_fetch_array($sth);
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

        $q = 'update hm2_deposits set type_id = '.$vv.', compound = '.$compound.' where id = '.$kk;
        db_query($q);
    }

    $releases = $frm['release'];
    while (list($kk, $vv) = each($releases)) {
        if ($vv == 0) {
            continue;
        }

        $q = 'select actual_amount, ec from hm2_deposits where id = '.$kk;
        $sth = db_query($q);
        if ($row = mysql_fetch_array($sth)) {
            $release_deposit = sprintf('%-.2f', $vv);
            if ($release_deposit <= $row['actual_amount']) {
                $q = 'insert into hm2_history set
    		user_id = '.$u_id.',
	    	amount = '.$release_deposit.',
    		type = \'early_deposit_release\',
	    	actual_amount = '.$release_deposit.',
        ec = '.$row['ec'].',
	    	date = now()';
                db_query($q);
                $dif = floor(($row['actual_amount'] - $release_deposit) * 100) / 100;
                if ($dif == 0) {
                    $q = 'update hm2_deposits set actual_amount = 0, amount = 0, status = \'off\' where id = '.$kk;
                } else {
                    $q = 'update hm2_deposits set actual_amount = actual_amount - '.$release_deposit.' where id = '.$kk;
                }

                db_query($q);
                continue;
            }

            continue;
        }
    }

    header(''.'Location: ?a=releasedeposits&u_id='.$u_id);
    exit;
}

/*
 * @action addbonuse_confirm
 */
if (($frm['a'] == 'addbonuse' and ($frm['action'] == 'addbonuse' or $frm['action'] == 'confirm'))) {
    $deposit = intval($frm['deposit']);
    $hyip_id = intval($frm['hyip_id']);
    if ($deposit == 1) {
        $q = 'select * from hm2_types where id = '.$hyip_id.' and status = \'on\'';
        $sth = db_query($q);
        $type = mysql_fetch_array($sth);
        if (!$type) {
            header('Location: ?a=send_bonuce&say=wrongplan');
            exit;
        }
    }

    session_start();
    if ($frm['action'] == 'addbonuse') {
        $code = substr($_SESSION['code'], 23, -32);
        if ($code === md5($frm['code'])) {
            $id = sprintf('%d', $frm['id']);
            $amount = sprintf('%f', $frm['amount']);
            $description = quote($frm['desc']);
            $ec = sprintf('%d', $frm['ec']);
            $q = 'insert into hm2_history set
              user_id = '.$id.',
              amount = '.$amount.',
              ec = '.$ec.',
              actual_amount = '.$amount.',
              type = \'bonus\',
              date = now(),
              description = \''.$description.'\'';
            if (!(db_query($q))) {
            }

            if ($deposit) {
                $delay = $type['delay'] - 1;
                if ($delay < 0) {
                    $delay = 0;
                }

                $user_id = $id;
                $q = 'insert into hm2_deposits set
             user_id = '.$user_id.',
             type_id = '.$hyip_id.',
             deposit_date = now(),
             last_pay_date = now()+ interval '.$delay.' day,
             status = \'on\',
             q_pays = 0,
             amount = \''.$amount.'\',
             actual_amount = \''.$amount.'\',
             ec = '.$ec.'
             ';
                db_query($q);
                $deposit_id = mysql_insert_id();
                $q = 'insert into hm2_history set
             user_id = '.$user_id.',
             amount = \'-'.$amount.'\',
             type = \'deposit\',
             description = \'Deposit to '.quote($type['name']).(''.'\',
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
            }

            if ($frm['inform'] == 1) {
                $q = 'select * from hm2_users where id = '.$id;
                $sth = db_query($q);
                $row = mysql_fetch_array($sth);
                $info = [];
                $info['name'] = $row['username'];
                $info['amount'] = number_format($amount, 2);
                send_template_mail('bonus', $row['email'], $settings['system_email'], $info);
            }

            header(''.'Location: ?a=addbonuse&say=done&id='.$id);
            exit;
        } else {
            $id = sprintf('%d', $frm['id']);
            header(''.'Location: ?a=addbonuse&id='.$id.'&say=invalid_code');
            exit;
        }
    }

    $code = '';
    if ($frm['action'] == 'confirm') {
        $account = preg_split('/,/', $frm['conf_email']);
        $conf_email = array_pop($account);
        $frm_env['HTTP_HOST'] = preg_replace('/www\\./', '', $frm_env['HTTP_HOST']);
        $conf_email .= '@'.$frm_env['HTTP_HOST'];
        $code = get_rand_md5(8);
        send_mail($conf_email, 'Bonus Confirmation Code', 'Code is: '.$code, 'From: '.$settings['system_email'].'
Reply-To: '.$settings['system_email']);
        $code = get_rand_md5(23).md5($code).get_rand_md5(32);
        $_SESSION['code'] = $code;
    }
}

/*
 * @action addpenality
 */
if (($frm['a'] == 'addpenality' and $frm['action'] == 'addpenality')) {
    $id = sprintf('%d', $frm['id']);
    $amount = sprintf('%f', abs($frm['amount']));
    $description = quote($frm['desc']);
    $ec = sprintf('%d', $frm['ec']);
    $q = 'insert into hm2_history set
	user_id = '.$id.',
	amount = -'.$amount.',
	actual_amount = -'.$amount.',
	ec = '.$ec.',
	type = \'penality\',
	date = now(),
	description = \''.$description.'\'';
    if (!(db_query($q))) {
    }

    if ($frm['inform'] == 1) {
        $q = 'select * from hm2_users where id = '.$id;
        $sth = db_query($q);
        $row = mysql_fetch_array($sth);
        $info = [];
        $info['name'] = $row['username'];
        $info['amount'] = number_format($amount, 2);
        send_template_mail('penalty', $row['email'], $settings['system_email'], $info);
    }

    header(''.'Location: ?a=addpenality&say=done&id='.$id);
    exit;
}

/*
 * @action deleteaccount
 */
if ($frm['a'] == 'deleteaccount') {
    $id = sprintf('%d', $frm['id']);
    $q = 'delete from hm2_users where id = '.$id.' and id <> 1';
    db_query($q);
    header('Location: ?a=members&q='.$frm['q'].'&p='.$frm['p'].'&status='.$frm['status']);
    exit;
}

/*
 * @action editaccount
 */
if (($frm['a'] == 'editaccount' and $frm['action'] == 'editaccount')) {
    $id = sprintf('%d', $frm['id']);
    if ((($settings['demomode'] == 1 and $id <= 3) and 0 < $id)) {
        header('Location: ?a=editaccount&id='.$frm['id']);
        exit;
    }

    $username = quote($frm['username']);
    $q = 'select * from hm2_users where id <> '.$id.' and username = \''.$username.'\'';
    $sth = db_query($q);
    ($row = mysql_fetch_array($sth));
    if ($row) {
        header('Location: ?a=editaccount&say=userexists&id='.$frm['id']);
        exit;
    }

    if (($frm['password'] != '' and $frm['password'] != $frm['password2'])) {
        header('Location: ?a=editaccount&say=incorrect_password&id='.$frm['id']);
        exit;
    }

    if (($frm['transaction_code'] != '' and $frm['transaction_code'] != $frm['transaction_code2'])) {
        header('Location: ?a=editaccount&say=incorrect_transaction_code&id='.$frm['id']);
        exit;
    }

    if ($id == 0) {
        $name = quote($frm['fullname']);
        $username = quote($frm['username']);
        $password = md5(quote($frm['password']));
        $egold = quote($frm['egold']);
        $perfectmoney = quote($frm['perfectmoney']);
        $evocash = quote($frm['evocash']);
        $intgold = quote($frm['intgold']);
        $stormpay = quote($frm['stormpay']);
        $ebullion = quote($frm['ebullion']);
        $paypal = quote($frm['paypal']);
        $goldmoney = quote($frm['goldmoney']);
        $eeecurrency = quote($frm['eeecurrency']);
        $email = quote($frm['email']);
        $status = quote($frm['status']);
        $auto_withdraw = sprintf('%d', $frm['auto_withdraw']);
        $admin_auto_pay_earning = sprintf('%d', $frm['admin_auto_pay_earning']);
        $pswd = '';
        if ($settings['store_uncrypted_password'] == 1) {
            $pswd = quote($frm['password']);
        }

        $q = 'insert into hm2_users set
  	name = \''.$name.'\',
  	username = \''.$username.'\',
	password = \''.$password.'\',
    egold_account = \''.$egold.'\',
  	perfectmoney_account = \''.$perfectmoney.'\',
	evocash_account = \''.$evocash.'\',
	intgold_account = \''.$intgold.'\',
	stormpay_account = \''.$stormpay.'\',
	ebullion_account = \''.$ebullion.'\',
	paypal_account = \''.$paypal.'\',
	goldmoney_account = \''.$goldmoney.'\',
	eeecurrency_account = \''.$eeecurrency.'\',
  	email = \''.$email.'\',
  	status = \''.$status.'\',
    auto_withdraw = '.$auto_withdraw.',
    admin_auto_pay_earning = '.$admin_auto_pay_earning.',
    user_auto_pay_earning = '.$admin_auto_pay_earning.',
    pswd = \''.$pswd.'\',
    date_register = now()';
        db_query($q);
        $frm['id'] = mysql_insert_id();
    } else {
        $q = 'select * from hm2_users where id = '.$id;
        $sth = db_query($q);
        ($row = mysql_fetch_array($sth));
        $name = quote($frm['fullname']);
        $address = quote($frm['address']);
        $city = quote($frm['city']);
        $state = quote($frm['state']);
        $zip = quote($frm['zip']);
        $country = quote($frm['country']);
        $edit_location = '';
        if ($settings['use_user_location']) {
            $edit_location = 'address = \''.$address.'\',
                        city = \''.$city.'\',
                        state = \''.$state.'\',
                        zip = \''.$zip.'\',
                        country = \''.$country.'\',
                       ';
        }

        $username = quote($frm['username']);
        $password = quote($frm['password']);
        $transaction_code = quote($frm['transaction_code']);
        $egold = quote($frm['egold']);
        $evocash = quote($frm['evocash']);
        $intgold = quote($frm['intgold']);
        $stormpay = quote($frm['stormpay']);
        $ebullion = quote($frm['ebullion']);
        $paypal = quote($frm['paypal']);
        $goldmoney = quote($frm['goldmoney']);
        $eeecurrency = quote($frm['eeecurrency']);
        $email = quote($frm['email']);
        $status = quote($frm['status']);
        $auto_withdraw = sprintf('%d', $frm['auto_withdraw']);
        $admin_auto_pay_earning = sprintf('%d', $frm['admin_auto_pay_earning']);
        $user_auto_pay_earning = $row['user_auto_pay_earning'];
        if (($row['admin_auto_pay_earning'] == 0 and $admin_auto_pay_earning == 1)) {
            $user_auto_pay_earning = 1;
        }

        $q = 'update hm2_users set
  	name = \''.$name.'\',
    '.$edit_location.'
  	username = \''.$username.'\',
  	egold_account = \''.$egold.'\',
	evocash_account = \''.$evocash.'\',
	intgold_account = \''.$intgold.'\',
	stormpay_account = \''.$stormpay.'\',
	ebullion_account = \''.$ebullion.'\',
	paypal_account = \''.$paypal.'\',
	goldmoney_account = \''.$goldmoney.'\',
	eeecurrency_account = \''.$eeecurrency.'\',
  	email = \''.$email.'\',
  	status = \''.$status.'\',
    auto_withdraw = '.$auto_withdraw.',
    admin_auto_pay_earning = '.$admin_auto_pay_earning.',
    user_auto_pay_earning = '.$user_auto_pay_earning.'
  	where id = '.$id.' and id <> 1';
        db_query($q);
        if ($password != '') {
            $pswd = quote($password);
            $password = md5($password);
            $q = 'update hm2_users set password = \''.$password.'\' where id = '.$id.' and id <> 1';
            db_query($q);
            if ($settings['store_uncrypted_password'] == 1) {
                $q = 'update hm2_users set pswd = \''.$pswd.'\' where id = '.$id.' and id <> 1';
                db_query($q);
            }
        }

        if ($transaction_code != '') {
            $pswd = quote($password);
            $password = md5($password);
            $q = 'update hm2_users set transaction_code = \''.$transaction_code.'\' where id = '.$id.' and id <> 1';
            db_query($q);
        }

        if ($frm['activate']) {
            $q = 'update hm2_users set activation_code = \'\', bf_counter = 0 where id = '.$id.' and id <> 1';
            db_query($q);
        }
    }

    header('Location: ?a=editaccount&id='.$frm['id'].'&say=saved');
    exit;
}

/*
 * @action members_modify_status
 */
if (($frm['a'] == 'members' and $frm['action'] == 'modify_status')) {
    if ($settings['demomode'] != 1) {
        $active = $frm['active'];
        while (list($id, $status) = each($active)) {
            $qstatus = quote($status);
            $q = 'update hm2_users set status = \''.$qstatus.'\' where id = '.$id;
            db_query($q);
        }
    }

    header('Location: ?a=members');
    exit;
}

/*
 * @action members_activate
 */
if (($frm['a'] == 'members' and $frm['action'] == 'activate')) {
    $active = $frm['activate'];
    while (list($id, $status) = each($active)) {
        $q = 'update hm2_users set activation_code = \'\', bf_counter = 0 where id = '.$id;
        db_query($q);
    }

    header('Location: ?a=members&status=blocked');
    exit;
}

/*
 * @action add_hyip
 */
if ($frm['action'] == 'add_hyip') {
    $q_days = sprintf('%d', $frm['hq_days']);
    if ($frm['hq_days_nolimit'] == 1) {
        $q_days = 0;
    }

    $min_deposit = sprintf('%0.2f', $frm['hmin_deposit']);
    $max_deposit = sprintf('%0.2f', $frm['hmax_deposit']);
    $return_profit = sprintf('%d', $frm['hreturn_profit']);
    $return_profit_percent = sprintf('%d', $frm['hreturn_profit_percent']);
    $percent = sprintf('%0.2f', $frm['hpercent']);
    $pay_to_egold_directly = sprintf('%d', $frm['earning_to_egold']);
    $use_compound = sprintf('%d', $frm['use_compound']);
    $work_week = sprintf('%d', $frm['work_week']);
    $parent = sprintf('%d', $frm['parent']);
    $desc = quote($frm_orig[plan_description]);
    $withdraw_principal = sprintf('%d', $frm['withdraw_principal']);
    $withdraw_principal_percent = sprintf('%.02f', $frm['withdraw_principal_percent']);
    $withdraw_principal_duration = sprintf('%d', $frm['withdraw_principal_duration']);
    $withdraw_principal_duration_max = sprintf('%d', $frm['withdraw_principal_duration_max']);
    $compound_min_deposit = sprintf('%.02f', $frm['compound_min_deposit']);
    $compound_max_deposit = sprintf('%.02f', $frm['compound_max_deposit']);
    $compound_percents_type = sprintf('%d', $frm['compound_percents_type']);
    $compound_min_percent = sprintf('%.02f', $frm['compound_min_percent']);
    if (($compound_min_percent < 0 or 100 < $compound_min_percent)) {
        $compound_min_percent = 0;
    }

    $compound_max_percent = sprintf('%.02f', $frm['compound_max_percent']);
    if (($compound_max_percent < 0 or 100 < $compound_max_percent)) {
        $compound_max_percent = 100;
    }

    $cps = preg_split('/\\s*,\\s*/', $frm['compound_percents']);
    $cps1 = [];
    foreach ($cps as $cp) {
        if (((!in_array($cp, $cps1) and 0 <= $cp) and $cp <= 100)) {
            array_push($cps1, sprintf('%d', $cp));
            continue;
        }
    }

    sort($cps1);
    $compound_percents = implode(',', $cps1);
    $hold = sprintf('%d', $frm[hold]);
    $delay = sprintf('%d', $frm[delay]);
    $q = 'insert into hm2_types set
	name=\''.quote($frm['hname']).(''.'\',
	q_days = '.$q_days.',
	period = \'').quote($frm['hperiod']).'\',
	status = \''.quote($frm['hstatus']).(''.'\',
	return_profit = \''.$return_profit.'\',
	return_profit_percent = '.$return_profit_percent.',
	pay_to_egold_directly = '.$pay_to_egold_directly.',
	use_compound = '.$use_compound.',
	work_week = '.$work_week.',
	parent = '.$parent.',
	withdraw_principal = '.$withdraw_principal.',
	withdraw_principal_percent = '.$withdraw_principal_percent.',
	withdraw_principal_duration = '.$withdraw_principal_duration.',
	withdraw_principal_duration_max = '.$withdraw_principal_duration_max.',
	compound_min_deposit = '.$compound_min_deposit.',
	compound_max_deposit = '.$compound_max_deposit.',
	compound_percents_type = '.$compound_percents_type.',
	compound_min_percent = '.$compound_min_percent.',
	compound_max_percent = '.$compound_max_percent.',
	compound_percents = \''.$compound_percents.'\',
	dsc = \''.$desc.'\',
	hold = '.$hold.',
	delay = '.$delay.'
  ');
    if (!(db_query($q))) {
    }

    $parent = mysql_insert_id();
    $rate_amount_active = $frm['rate_amount_active'];
    for ($i = 0; $i < 300; ++$i) {
        if ($frm['rate_amount_active'][$i] == 1) {
            $name = quote($frm['rate_amount_name'][$i]);
            $min_amount = sprintf('%0.2f', $frm['rate_min_amount'][$i]);
            $max_amount = sprintf('%0.2f', $frm['rate_max_amount'][$i]);
            $percent = sprintf('%0.2f', $frm['rate_percent'][$i]);
            $q = 'insert into hm2_plans set
		parent='.$parent.',
		name=\''.$name.'\',
		min_deposit = '.$min_amount.',
		max_deposit = '.$max_amount.',
		percent = '.$percent;
            if (!(db_query($q))) {
            }

            continue;
        }
    }

    header('Location: ?a=rates');
    exit;
}

/*
 * @action edit_hyip
 */
if ($frm['action'] == 'edit_hyip') {
    $id = sprintf('%d', $frm['hyip_id']);
    if (($id < 3 and $settings['demomode'] == 1)) {
        header('Location: ?a=rates');
        exit;
    }

    $q_days = sprintf('%d', $frm['hq_days']);
    if ($frm['hq_days_nolimit'] == 1) {
        $q_days = 0;
    }

    $min_deposit = sprintf('%0.2f', $frm['hmin_deposit']);
    $max_deposit = sprintf('%0.2f', $frm['hmax_deposit']);
    $return_profit = sprintf('%d', $frm['hreturn_profit']);
    $return_profit_percent = sprintf('%d', $frm['hreturn_profit_percent']);
    $pay_to_egold_directly = sprintf('%d', $frm['earning_to_egold']);
    $percent = sprintf('%0.2f', $frm['hpercent']);
    $work_week = sprintf('%d', $frm['work_week']);
    $use_compound = sprintf('%d', $frm['use_compound']);
    $parent = sprintf('%d', $frm['parent']);
    $desc = quote($frm_orig[plan_description]);
    $withdraw_principal = sprintf('%d', $frm['withdraw_principal']);
    $withdraw_principal_percent = sprintf('%.02f', $frm['withdraw_principal_percent']);
    $withdraw_principal_duration = sprintf('%d', $frm['withdraw_principal_duration']);
    $withdraw_principal_duration_max = sprintf('%d', $frm['withdraw_principal_duration_max']);
    $compound_min_deposit = sprintf('%.02f', $frm['compound_min_deposit']);
    $compound_max_deposit = sprintf('%.02f', $frm['compound_max_deposit']);
    $compound_percents_type = sprintf('%d', $frm['compound_percents_type']);
    $compound_min_percent = sprintf('%.02f', $frm['compound_min_percent']);
    if (($compound_min_percent < 0 or 100 < $compound_min_percent)) {
        $compound_min_percent = 0;
    }

    $compound_max_percent = sprintf('%.02f', $frm['compound_max_percent']);
    if (($compound_max_percent < 0 or 100 < $compound_max_percent)) {
        $compound_max_percent = 100;
    }

    $cps = preg_split('/\\s*,\\s*/', $frm['compound_percents']);
    $cps1 = [];
    foreach ($cps as $cp) {
        if (((!in_array($cp, $cps1) and 0 <= $cp) and $cp <= 100)) {
            array_push($cps1, sprintf('%d', $cp));
            continue;
        }
    }

    sort($cps1);
    $compound_percents = implode(',', $cps1);
    $closed = ($frm['closed'] ? 1 : 0);
    $hold = sprintf('%d', $frm[hold]);
    $delay = sprintf('%d', $frm[delay]);
    $q = 'update hm2_types set
	name=\''.quote($frm['hname']).(''.'\',
	q_days = '.$q_days.',
	period = \'').quote($frm['hperiod']).'\',
	status = \''.quote($frm['hstatus']).(''.'\',
	return_profit = \''.$return_profit.'\',
	return_profit_percent = '.$return_profit_percent.',
	pay_to_egold_directly = '.$pay_to_egold_directly.',
	use_compound = '.$use_compound.',
	work_week = '.$work_week.',
	parent = '.$parent.',
  withdraw_principal = '.$withdraw_principal.',
  withdraw_principal_percent = '.$withdraw_principal_percent.',
  withdraw_principal_duration = '.$withdraw_principal_duration.',
  withdraw_principal_duration_max = '.$withdraw_principal_duration_max.',
  compound_min_deposit = '.$compound_min_deposit.',
  compound_max_deposit = '.$compound_max_deposit.',
  compound_percents_type = '.$compound_percents_type.',
  compound_min_percent = '.$compound_min_percent.',
  compound_max_percent = '.$compound_max_percent.',
  compound_percents = \''.$compound_percents.'\',
  closed = '.$closed.',

  dsc=\''.$desc.'\',
  hold = '.$hold.',
  delay = '.$delay.'

	 where id='.$id.'
  ');
    if (!(db_query($q))) {
    }

    $parent = $id;
    $q = 'delete from hm2_plans where parent = '.$id;
    if (!(db_query($q))) {
    }

    $rate_amount_active = $frm['rate_amount_active'];
    for ($i = 0; $i < 300; ++$i) {
        if ($frm['rate_amount_active'][$i] == 1) {
            $name = quote($frm['rate_amount_name'][$i]);
            $min_amount = sprintf('%0.2f', $frm['rate_min_amount'][$i]);
            $max_amount = sprintf('%0.2f', $frm['rate_max_amount'][$i]);
            $percent = sprintf('%0.2f', $frm['rate_percent'][$i]);
            $q = 'insert into hm2_plans set
		parent='.$parent.',
		name=\''.$name.'\',
		min_deposit = '.$min_amount.',
		max_deposit = '.$max_amount.',
		percent = '.$percent;
            if (!(db_query($q))) {
            }

            continue;
        }
    }

    header('Location: ?a=rates');
    exit;
}

/*
 * @action thistory_download_csv
 */
if (($frm['a'] == 'thistory' and $frm['action2'] == 'download_csv')) {
    $frm['day_to'] = sprintf('%d', $frm['day_to']);
    $frm['month_to'] = sprintf('%d', $frm['month_to']);
    $frm['year_to'] = sprintf('%d', $frm['year_to']);
    $frm['day_from'] = sprintf('%d', $frm['day_from']);
    $frm['month_from'] = sprintf('%d', $frm['month_from']);
    $frm['year_from'] = sprintf('%d', $frm['year_from']);
    if ($frm['day_to'] == 0) {
        $frm['day_to'] = date('j', time() + $settings['time_dif'] * 60 * 60);
        $frm['month_to'] = date('n', time() + $settings['time_dif'] * 60 * 60);
        $frm['year_to'] = date('Y', time() + $settings['time_dif'] * 60 * 60);
        $frm['day_from'] = 1;
        $frm['month_from'] = $frm['month_to'];
        $frm['year_from'] = $frm['year_to'];
    }

    $datewhere = '\''.$frm['year_from'].'-'.$frm['month_from'].'-'.$frm['day_from'].'\' + interval 0 day < date + interval '.$settings['time_dif'].' hour and '.'\''.$frm['year_to'].'-'.$frm['month_to'].'-'.$frm['day_to'].'\' + interval 1 day > date + interval '.$settings['time_dif'].' hour ';
    if ($frm['ttype'] != '') {
        if ($frm['ttype'] == 'exchange') {
            $typewhere = ' and (type=\'exchange_out\' or type=\'exchange_in\')';
        } else {
            $typewhere = ' and type=\''.quote($frm['ttype']).'\' ';
        }
    }

    $u_id = sprintf('%d', $frm['u_id']);
    if (1 < $u_id) {
        $userwhere = ' and user_id = '.$u_id.' ';
    }

    $ecwhere = '';
    if ($frm[ec] == '') {
        $frm[ec] = -1;
    }

    $ec = sprintf('%d', $frm[ec]);
    if (-1 < $frm[ec]) {
        $ecwhere = ' and ec = '.$ec;
    }

    $q = 'select *, date_format(date + interval '.$settings['time_dif'].(''.' hour, \'%b-%e-%Y %r\') as d from hm2_history where '.$datewhere.' '.$userwhere.' '.$typewhere.' '.$ecwhere.' order by date desc, id desc');
    $sth = db_query($q);
    $trans = [];
    while ($row = mysql_fetch_array($sth)) {
        $q = 'select username from hm2_users where id = '.$row['user_id'];
        $sth1 = db_query($q);
        $row1 = mysql_fetch_array($sth1);
        if ($row1) {
            $row['username'] = $row1['username'];
        } else {
            $row['username'] = '-- deleted user --';
        }

        array_push($trans, $row);
    }

    $from = $frm['month_from'].'_'.$frm['day_from'].'_'.$frm['year_from'];
    $to = $frm['month_to'].'_'.$frm['day_to'].'_'.$frm['year_to'];
    header('Content-Disposition: attachment; filename='.$frm['ttype'].(''.'history-'.$from.'-'.$to.'.csv'));
    header('Content-type: text/coma-separated-values');
    echo '"Transaction Type","User","Amount","Currency","Date","Description"';
    for ($i = 0; $i < count($trans); ++$i) {
        echo '"'.$transtype[$trans[$i]['type']].'","'.$trans[$i]['username'].'","$'.number_format(abs($trans[$i]['actual_amount']),
                2).'","'.$exchange_systems[$trans[$i]['ec']]['name'].'","'.$trans[$i]['d'].'","'.$trans[$i]['description'].'"'.'';
    }

    exit;
}

/*
 * @action add_processing
 */
if (($frm['a'] == 'add_processing' and $frm[action] == 'add_processing')) {
    if (!$settings['demomode']) {
        $status = ($frm['status'] ? 1 : 0);
        $name = quote($frm['name']);
        $description = quote($frm_orig['description']);
        $use = $frm['field'];
        $fields = [];
        if ($use) {
            reset($use);
            $i = 1;
            foreach ($use as $id => $value) {
                if ($frm['use'][$id]) {
                    $fields[$i] = $value;
                    ++$i;
                    continue;
                }
            }
        }

        $qfields = serialize($fields);
        $q = 'select max(id) as max_id from hm2_processings';
        $sth = db_query($q);
        $row = mysql_fetch_array($sth);
        $max_id = $row['max_id'];
        if ($max_id < 999) {
            $max_id = 998;
        }

        ++$max_id;
        $q = 'insert into hm2_processings set
             id = '.$max_id.',
             status = '.$status.',
             name = \''.$name.'\',
             description = \''.$description.'\',
             infofields = \''.quote($qfields).'\'
         ';
        db_query($q);
    }

    header('Location: ?a=processings');
    exit;
}

/*
 * @action edit_processing
 */
if (($frm['a'] == 'edit_processing' and $frm[action] == 'edit_processing')) {
    if (!$settings['demomode']) {
        $pid = intval($frm['pid']);
        $status = ($frm['status'] ? 1 : 0);
        $name = quote($frm['name']);
        $description = quote($frm_orig['description']);
        $use = $frm['field'];
        $fields = [];
        if ($use) {
            reset($use);
            $i = 1;
            foreach ($use as $id => $value) {
                if ($frm['use'][$id]) {
                    $fields[$i] = $value;
                    ++$i;
                    continue;
                }
            }
        }

        $qfields = serialize($fields);
        $q = 'update hm2_processings set
             status = '.$status.',
             name = \''.$name.'\',
             description = \''.$description.'\',
             infofields = \''.quote($qfields).(''.'\'
           where id = '.$pid.'
         ');
        db_query($q);
    }

    header('Location: ?a=processings');
    exit;
}

/*
 * @action update_processings
 */
if ($frm['a'] == 'update_processings') {
    if (!$settings['demomode']) {
        $q = 'update hm2_processings set status = 0';
        db_query($q);
        $status = $frm['status'];
        if ($status) {
            foreach ($status as $id => $v) {
                $q = 'update hm2_processings set status = 1 where id = '.$id;
                db_query($q);
            }
        }
    }

    header('Location: ?a=processings');
    exit;
}

/*
 * @action delete_processing
 */
if ($frm['a'] == 'delete_processing') {
    if (!$settings['demomode']) {
        $pid = intval($frm['pid']);
        $q = 'delete from hm2_processings where id = '.$pid;
        db_query($q);
    }

    header('Location: ?a=processings');
    exit;
}

include app_path('Hm').'/inc/admin/html.header.inc.php';
echo '
  <tr>
    <td valign="top">
	 <table cellspacing=0 cellpadding=1 border=0 width=100% height=100% bgcolor=#ff8d00>
	   <tr>
	     <td>
           <table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
             <tr bgcolor="#FFFFFF" valign="top">
              <td width=300 align=center>
				   <!-- Image Table: Start -->';
include app_path('Hm').'/inc/admin/menu.inc.php';
echo '				   <br>

              </td>
              <td bgcolor="#ff8d00" valign="top" width=1><img src=images/q.gif width=1 height=1></td>
              <td bgcolor="#FFFFFF" valign="top" width=99%>
            <!-- Main: Start -->
            <table width="100%" height="100%" border="0" cellpadding="10" cellspacing="0" class="forTexts">
              <tr>
                <td width=100% height';
echo '=100% valign=top>';

switch ($frm['a']) {
    case 'rates':
        include app_path('Hm').'/inc/admin/rates.inc.php';
        break;
    case 'editrate':
        include app_path('Hm').'/inc/admin/edit_hyip.inc.php';
        break;
    case 'add_hyip':
        include app_path('Hm').'/inc/admin/add_hyip.inc.php';
        break;
    case 'members':
        include app_path('Hm').'/inc/admin/members.inc.php';
        break;
    case 'editaccount':
        include app_path('Hm').'/inc/admin/editaccount.inc.php';
        break;
    case 'addmember':
        include app_path('Hm').'/inc/admin/addmember.inc.php';
        break;
    case 'userexists':
        include app_path('Hm').'/inc/admin/error_userexists.inc.php';
        break;
    case 'userfunds':
        include app_path('Hm').'/inc/admin/manage_user_funds.inc.php';
        break;
    case 'addbonuse':
        include app_path('Hm').'/inc/admin/addbonuse.inc.php';
        break;
    case 'mass':
        include app_path('Hm').'/inc/admin/prepare_mass_pay.inc.php';
        break;
    case 'thistory':
        include app_path('Hm').'/inc/admin/transactions_history.php';
        break;
    case 'addpenality':
        include app_path('Hm').'/inc/admin/addpenality.inc.php';
        break;
    case 'releasedeposits':
        include app_path('Hm').'/inc/admin/releaseusersdeposits.inc.php';
        break;
    case 'pay_withdraw':
        include app_path('Hm').'/inc/admin/process_withdraw.php';
        break;
    case 'settings':
        include app_path('Hm').'/inc/admin/settings.inc.php';
        break;
    case 'info_box':
        include app_path('Hm').'/inc/admin/info_box_settings.inc.php';
        break;
    case 'send_bonuce':
        include app_path('Hm').'/inc/admin/send_bonuce.inc.php';
        break;
    case 'send_penality':
        include app_path('Hm').'/inc/admin/send_penality.inc.php';
        break;
    case 'newsletter':
        include app_path('Hm').'/inc/admin/newsletter.inc.php';
        break;
    case 'edit_emails':
        include app_path('Hm').'/inc/admin/emails.inc.php';
        break;
    case 'referal':
        include app_path('Hm').'/inc/admin/referal.inc.php';
        break;
    case 'auto-pay-settings':
        include app_path('Hm').'/inc/admin/auto_pay_settings.inc.php';
        break;
    case 'error_pay_log':
        include app_path('Hm').'/inc/admin/error_pay_log.inc.php';
        break;
    case 'news':
        include app_path('Hm').'/inc/admin/news.inc.php';
        break;
    case 'wire_settings':
        include app_path('Hm').'/inc/admin/wire_settings.inc.php';
        break;
    case 'wires':
        include app_path('Hm').'/inc/admin/wires.inc.php';
        break;
    case 'wiredetails':
        include app_path('Hm').'/inc/admin/wiredetails.inc.php';
        break;
    case 'affilates':
        include app_path('Hm').'/inc/admin/affilates.inc.php';
        break;
    case 'custompages':
        include app_path('Hm').'/inc/admin/custompage.inc.php';
        break;
    case 'exchange_rates':
        include app_path('Hm').'/inc/admin/exchange_rates.inc.php';
        break;
    case 'security':
        include app_path('Hm').'/inc/admin/security.inc.php';
        break;
    case 'processings':
        include app_path('Hm').'/inc/admin/processings.inc.php';
        break;
    case 'add_processing':
        include app_path('Hm').'/inc/admin/add_processing.inc.php';
        break;
    case 'edit_processing':
        include app_path('Hm').'/inc/admin/edit_processing.inc.php';
        break;
    case 'pending_deposits':
        include app_path('Hm').'/inc/admin/pending_deposits.inc.php';
        break;
    case 'pending_deposit_details':
        include app_path('Hm').'/inc/admin/pending_deposit_details.inc.php';
        break;
    case 'startup_bonus':
        include app_path('Hm').'/inc/admin/startup_bonus.inc.php';
        break;
    default:
        include app_path('Hm').'/inc/admin/main.inc.php';
}
echo '</td></tr></table><!-- Main: END --></td></tr></table></td></tr></table></td></tr>';
include app_path('Hm').'/inc/admin/html.footer.inc.php';
