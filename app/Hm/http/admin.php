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
use Illuminate\Support\Facades\Auth;
use App\Exceptions\RedirectException;

$admin_url = env('ADMIN_URL');

require app_path('Hm').'/lib/admin.php';

$userinfo = [];
$userinfo['logged'] = 0;

try_auth($userinfo);
$username = $userinfo['username'];

/*
 * @action logout
 */
if (app('data')->frm['a'] == 'logout') {
    Auth::logout();
    throw new RedirectException('/');
}

/*
 * @action showprogramstat
 */
if (app('data')->frm['a'] == 'showprogramstat') {
    show_program_stat();
    throw new EmptyException();
}

/*
 * @action startup_bonus_set
 */
if ((app('data')->frm['a'] == 'startup_bonus' and app('data')->frm['act'] == 'set')) {
    startup_bonus();
    throw new EmptyException();
}

/*
 * @action exchange_rates_save
 */
if ((app('data')->frm['a'] == 'exchange_rates' and app('data')->frm['action'] == 'save')) {
    save_exchange_rates();
    throw new EmptyException();
}

if ($userinfo['should_count'] == 1) {
    $q = 'update users set last_access_time = now() where username=\''.$username.'\'';
    db_query($q);

    count_earning(-1);
}

/*
 * @action affilates_remove_ref
 */
if ((app('data')->frm['a'] == 'affilates' and app('data')->frm['action'] == 'remove_ref')) {
    $u_id = sprintf('%d', app('data')->frm['u_id']);
    $ref = sprintf('%d', app('data')->frm['ref']);
    $q = 'update users set ref = 0 where id = '.$ref;
    db_query($q);
    throw new RedirectException($admin_url.'?a=affilates&u_id='.$u_id);
}

/*
 * @action affilates_change_upline
 */
if ((app('data')->frm['a'] == 'affilates' and app('data')->frm['action'] == 'change_upline')) {
    $u_id = sprintf('%d', app('data')->frm['u_id']);
    $upline = quote(app('data')->frm['upline']);
    $q = 'select * from users where username=\''.$upline.'\'';
    $sth = db_query($q);
    $id = 0;
    while ($row = mysql_fetch_array($sth)) {
        $id = $row['id'];
    }

    $q = 'update users set ref = '.$id.' where id = '.$u_id;
    db_query($q);
    throw new RedirectException($admin_url.'?a=affilates&u_id='.$u_id);
}

/*
 * @action pending_deposit_details_movetoproblem
 */
if ((app('data')->frm['a'] == 'pending_deposit_details' and app('data')->frm['action'] == 'movetoproblem')) {
    $id = sprintf('%d', app('data')->frm['id']);
    $q = 'update pending_deposits set status=\'problem\' where id = '.$id;
    db_query($q);
    throw new RedirectException($admin_url.'?a=pending_deposits');
}

/*
 * @action pending_deposit_details_movetonew
 */
if ((app('data')->frm['a'] == 'pending_deposit_details' and app('data')->frm['action'] == 'movetonew')) {
    $id = sprintf('%d', app('data')->frm['id']);
    $q = 'update pending_deposits set status=\'new\' where id = '.$id;
    db_query($q);
    throw new RedirectException($admin_url.'?a=pending_deposits&type=problem');
}

/*
 * @action pending_deposit_details_delete
 */
if ((app('data')->frm['a'] == 'pending_deposit_details' and app('data')->frm['action'] == 'delete')) {
    $id = sprintf('%d', app('data')->frm['id']);
    $q = 'delete from pending_deposits where id = '.$id;
    db_query($q);
    throw new RedirectException($admin_url.'?a=pending_deposits&type='.app('data')->frm['type']);
}

/*
 * @action pending_deposit_details_movetodeposit_movetoaccount_yes
 */
if (((app('data')->frm['a'] == 'pending_deposit_details' and (app('data')->frm['action'] == 'movetodeposit' or app('data')->frm['action'] == 'movetoaccount')) and app('data')->frm['confirm'] == 'yes')) {
    $deposit_id = $id = sprintf('%d', app('data')->frm['id']);
    $q = 'select
              pending_deposits.*,
              users.username
        from
          pending_deposits,
          users
        where
          pending_deposits.user_id = users.id and
          pending_deposits.id = '.$id.' and
          pending_deposits.status != \'processed\'';
    $sth = db_query($q);
    $amount = sprintf('%0.2f', app('data')->frm['amount']);
    while ($row = mysql_fetch_array($sth)) {
        $ps = $row['ec'];
        $username = $row['username'];
        $compound = sprintf('%d', $row['compound']);
        $fields = $row['fields'];
        $user_id = $row['user_id'];
        if ((100 < $compound or $compound < 0)) {
            $compound = 0;
        }

        $q = 'insert into history set
                user_id = '.$row['user_id'].(',
                date = now(),
                amount = '.$amount.',
                actual_amount = '.$amount.',
                type=\'add_funds\',
                description=\'').quote(app('data')->exchange_systems[$row['ec']]['name']).' transfer received\',
                ec = '.$row['ec'];
        db_query($q);
        if ((app('data')->frm['action'] == 'movetodeposit' and 0 < $row[type_id])) {
            $q = 'select name, delay from types where id = '.$row['type_id'];
            ($sth1 = db_query($q));
            $row1 = mysql_fetch_array($sth1);
            $delay = $row1[delay];
            if (0 < $delay) {
                --$delay;
            }

            $q = 'insert into deposits set
                      user_id = '.$row['user_id'].',
                      type_id = '.$row['type_id'].(',
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
            $q = 'insert into history set
                      user_id = '.$row['user_id'].(',
                      date = now(),
                      amount = -'.$amount.',
                      actual_amount = -'.$amount.',
                      type=\'deposit\',
                      description=\'Deposit to ').quote($row1[name]).('\',
                      ec = '.$ps.',
                      deposit_id = '.$deposit_id);
            db_query($q);
            $ref_sum = referral_commission($row['user_id'], $amount, $ps);
        }

        $info = [];
        $q = 'select * from users where id = '.$user_id;
        $sth1 = db_query($q);
        $userinfo = mysql_fetch_array($sth1);
        $q = 'select * from types where id = '.$row['type_id'];
        $sth1 = db_query($q);
        $type = mysql_fetch_array($sth1);
        $info['username'] = $userinfo['username'];
        $info['name'] = $userinfo['name'];
        $info['amount'] = number_format($row['amount'], 2);
        $info['currency'] = app('data')->exchange_systems[$ps]['name'];
        $info['compound'] = number_format($type['compound'], 2);
        $info['plan'] = (0 < $row[type_id] ? $type['name'] : 'Deposit to Account');
        $q = 'select * from processings where id = '.$row['ec'];
        $sth = db_query($q);
        $processing = mysql_fetch_array($sth);
        $pfields = unserialize($processing['infofields']);
        $infofields = unserialize($fields);
        $f = '';
        foreach ($pfields as $id => $name) {
            $f .= $name.': '.stripslashes($infofields[$id]);
        }

        $info['fields'] = $f;
        $q = 'select date_format(date + interval '.app('data')->settings['time_dif'].' hour, \'%b-%e-%Y %r\') as dd from pending_deposits where id = '.$row['id'];
        ($sth1 = db_query($q));
        $row1 = mysql_fetch_array($sth1);
        $info['deposit_date'] = $row1['dd'];
        $q = 'select email from users where id = 1';
        $sth1 = db_query($q);
        $admin_row = mysql_fetch_array($sth1);
        send_template_mail('deposit_approved_admin_notification', $admin_row['email'], $info);
        send_template_mail('deposit_approved_user_notification', $userinfo['email'], $info);
    }

    $id = sprintf('%d', app('data')->frm['id']);
    $q = 'update pending_deposits set status=\'processed\' where id = '.$id;
    db_query($q);
    throw new RedirectException($admin_url.'?a=pending_deposits');
}

/*
 * @action mass
 */
if (app('data')->frm['a'] == 'mass') {
    if (app('data')->frm['action2'] == 'massremove') {
        $ids = app('data')->frm['pend'];
        reset($ids);
        while (list($kk, $vv) = each($ids)) {
            $q = 'delete from history where id = '.$kk;
            db_query($q);
        }

        throw new RedirectException($admin_url.'?a=thistory&ttype=withdraw_pending&say=massremove');
    }

    if (app('data')->frm['action2'] == 'masssetprocessed') {
        $ids = app('data')->frm['pend'];
        reset($ids);
        while (list($kk, $vv) = each($ids)) {
            $q = 'select * from history where id = '.$kk;
            $sth = db_query($q);
            while ($row = mysql_fetch_array($sth)) {
                $q = 'insert into history set
                        user_id = '.$row['user_id'].',
                        amount = -'.abs($row['actual_amount']).',
                        actual_amount = -'.abs($row['actual_amount']).',
                        type = \'withdrawal\',
                        date = now(),
                        description = \'Withdrawal processed\',
                        ec = '.$row['ec'];
                db_query($q);
                $q = 'delete from history where id = '.$row['id'];
                db_query($q);
                $userinfo = [];
                $q = 'select * from users where id = '.$row['user_id'];
                $sth1 = db_query($q);
                $userinfo = mysql_fetch_array($sth1);
                $info = [];
                $info['username'] = $userinfo['username'];
                $info['name'] = $userinfo['name'];
                $info['amount'] = number_format(abs($row['amount']), 2);
                $info['currency'] = app('data')->exchange_systems[$row['ec']]['name'];
                $info['account'] = 'n/a';
                $info['batch'] = 'n/a';
                $info['paying_batch'] = 'n/a';
                $info['receiving_batch'] = 'n/a';
                send_template_mail('withdraw_user_notification', $userinfo['email'], $info);
                $q = 'select email from users where id = 1';
                $sth = db_query($q);
                $admin_row = mysql_fetch_array($sth);
                send_template_mail('withdraw_admin_notification', $admin_row['email'], $info);
            }
        }

        throw new RedirectException($admin_url.'?a=thistory&ttype=withdraw_pending&say=massprocessed');
    }

    if (app('data')->frm['action2'] == 'masscsv') {
        $ids = app('data')->frm['pend'];
        if (! $ids) {
            echo 'Nothing selected.';
            throw new EmptyException();
        }

        reset($ids);
        header('Content-type: text/plain');
        $ec = -1;
        $s = '-1';
        while (list($kk, $vv) = each($ids)) {
            $s .= ','.$kk;
        }

        $q = 'select h.*, u.perfectmoney_account, u.payeer_account, u.bitcoin_account,
                from history as h, users as u where h.id in ('.$s.') and u.id = h.user_id order by ec';
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            if ($ec != $row['ec']) {
                echo '#'.app('data')->exchange_systems[$row['ec']]['name'].' transactions (account, amount)';
                $ec = $row['ec'];
            }

            switch ($row['ec']) {
                case 1:
                    $ac = $row['perfectmoney_account'];
                    break;
                case 2:
                    $ac = $row['payeer_account'];
                    break;
                case 3:
                    $ac = $row['bitcoin_account'];
                    break;
            }

            $amount = abs($row['amount']);
            $fee = floor($amount * app('data')->settings['withdrawal_fee']) / 100;
            if ($fee < app('data')->settings['withdrawal_fee_min']) {
                $fee = app('data')->settings['withdrawal_fee_min'];
            }

            $to_withdraw = $amount - $fee;
            if ($to_withdraw < 0) {
                $to_withdraw = 0;
            }

            $to_withdraw = sprintf('%.02f', floor($to_withdraw * 100) / 100);
            echo $ac.','.abs($to_withdraw);
        }

        throw new EmptyException();
    }
}

/*
 * @action referal_change
 */
if ((app('data')->frm['a'] == 'referal' and app('data')->frm['action'] == 'change')) {
    if (app('data')->settings['demomode'] == 1) {
    } else {
        $q = 'delete from referal where level = 1';
        db_query($q);
        for ($i = 0; $i < 300; ++$i) {
            if (app('data')->frm['active'][$i] == 1) {
                $qname = quote(app('data')->frm['ref_name'][$i]);
                $from = sprintf('%d', app('data')->frm['ref_from'][$i]);
                $to = sprintf('%d', app('data')->frm['ref_to'][$i]);
                $percent = sprintf('%0.2f', app('data')->frm['ref_percent'][$i]);
                $percent_daily = sprintf('%0.2f', app('data')->frm['ref_percent_daily'][$i]);
                $percent_weekly = sprintf('%0.2f', app('data')->frm['ref_percent_weekly'][$i]);
                $percent_monthly = sprintf('%0.2f', app('data')->frm['ref_percent_monthly'][$i]);
                $q = 'insert into referal set
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

        app('data')->settings['use_referal_program'] = sprintf('%d', app('data')->frm['usereferal']);
        app('data')->settings['force_upline'] = sprintf('%d', app('data')->frm['force_upline']);
        app('data')->settings['get_rand_ref'] = sprintf('%d', app('data')->frm['get_rand_ref']);
        app('data')->settings['use_active_referal'] = sprintf('%d', app('data')->frm['useactivereferal']);
        app('data')->settings['pay_active_referal'] = sprintf('%d', app('data')->frm['payactivereferal']);
        app('data')->settings['use_solid_referral_commission'] = sprintf('%d', app('data')->frm['use_solid_referral_commission']);
        app('data')->settings['solid_referral_commission_amount'] = sprintf('%.02f', app('data')->frm['solid_referral_commission_amount']);
        app('data')->settings['ref2_cms'] = sprintf('%0.2f', app('data')->frm['ref2_cms']);
        app('data')->settings['ref3_cms'] = sprintf('%0.2f', app('data')->frm['ref3_cms']);
        app('data')->settings['ref4_cms'] = sprintf('%0.2f', app('data')->frm['ref4_cms']);
        app('data')->settings['ref5_cms'] = sprintf('%0.2f', app('data')->frm['ref5_cms']);
        app('data')->settings['ref6_cms'] = sprintf('%0.2f', app('data')->frm['ref6_cms']);
        app('data')->settings['ref7_cms'] = sprintf('%0.2f', app('data')->frm['ref7_cms']);
        app('data')->settings['ref8_cms'] = sprintf('%0.2f', app('data')->frm['ref8_cms']);
        app('data')->settings['ref9_cms'] = sprintf('%0.2f', app('data')->frm['ref9_cms']);
        app('data')->settings['ref10_cms'] = sprintf('%0.2f', app('data')->frm['ref10_cms']);
        app('data')->settings['show_referals'] = sprintf('%d', app('data')->frm['show_referals']);
        app('data')->settings['show_refstat'] = sprintf('%d', app('data')->frm['show_refstat']);
        save_settings();
    }

    throw new RedirectException($admin_url.'?a=referal');
}

/*
 * @action deleterate
 */
if (app('data')->frm['a'] == 'deleterate') {
    $id = sprintf('%d', app('data')->frm['id']);
    if (($id < 3 and app('data')->settings['demomode'] == 1)) {
    } else {
        $q = 'delete from types where id = '.$id;
        db_query($q);
        $q = 'delete from plans where parent = '.$id;
        db_query($q);
    }

    throw new RedirectException($admin_url.'?a=rates');
}

/*
 * @action send_bonuce_send_bonuce_confirm
 */
if ((app('data')->frm['a'] == 'send_bonuce' and (app('data')->frm['action'] == 'send_bonuce' or app('data')->frm['action'] == 'confirm'))) {
    $amount = sprintf('%0.2f', app('data')->frm['amount']);
    if ($amount == 0) {
        throw new RedirectException($admin_url.'?a=send_bonuce&say=wrongamount');
    }

    $deposit = intval(app('data')->frm['deposit']);
    $hyip_id = intval(app('data')->frm['hyip_id']);
    if ($deposit == 1) {
        $q = 'select * from types where id = '.$hyip_id.' and status = \'on\'';
        $sth = db_query($q);
        $type = mysql_fetch_array($sth);
        if (! $type) {
            throw new RedirectException($admin_url.'?a=send_bonuce&say=wrongplan');
        }
    }

    $ec = sprintf('%d', app('data')->frm['ec']);
    if (app('data')->frm['to'] == 'user') {
        $q = 'select * from users where username = \''.quote(app('data')->frm['username']).'\'';
    } else {
        if (app('data')->frm['to'] == 'all') {
            $q = 'select * from users where id > 1';
        } else {
            if (app('data')->frm['to'] == 'active') {
                $q = 'select users.* from users, deposits where users.id > 1 and deposits.user_id = users.id group by users.id';
            } else {
                if (app('data')->frm['to'] == 'passive') {
                    $q = 'select u.* from users as u left outer join deposits as d on u.id = d.user_id where u.id > 1 and d.user_id is NULL';
                } else {
                    throw new RedirectException($admin_url.'?a=send_bonuce&say=someerror');
                }
            }
        }
    }

    if (app('data')->frm['action'] == 'send_bonuce') {
        $code = substr(session('code'), 23, -32);
        if ($code === md5(app('data')->frm['code'])) {
            $sth = db_query($q);
            $flag = 0;
            $total = 0;
            $description = quote(app('data')->frm['description']);
            while ($row = mysql_fetch_array($sth)) {
                $flag = 1;
                $total += $amount;
                $q = 'insert into history set
                        user_id = '.$row['id'].',
                        amount = '.$amount.',
                        description = \''.$description.'\',
                        type=\'bonus\',
                        actual_amount = '.$amount.',
                        ec = '.$ec.',
                        date = now()';
                db_query($q);
                if ($deposit) {
                    $delay = $type['delay'] - 1;
                    if ($delay < 0) {
                        $delay = 0;
                    }

                    $user_id = $row['id'];
                    $q = 'insert into deposits set
                            user_id = '.$user_id.',
                            type_id = '.$hyip_id.',
                            deposit_date = now(),
                            last_pay_date = now()+ interval '.$delay.' day,
                            status = \'on\',
                            q_pays = 0,
                            amount = \''.$amount.'\',
                            actual_amount = \''.$amount.'\',
                            ec = '.$ec;
                    db_query($q);
                    $deposit_id = mysql_insert_id();
                    $q = 'insert into history set
                            user_id = '.$user_id.',
                            amount = \'-'.$amount.'\',
                            type = \'deposit\',
                            description = \'Deposit to '.quote($type['name']).('\',
                            actual_amount = -'.$amount.',
                            ec = '.$ec.',
                            date = now(),
                            deposit_id = '.$deposit_id);
                    db_query($q);
                    if (app('data')->settings['banner_extension'] == 1) {
                        $imps = 0;
                        if (0 < app('data')->settings['imps_cost']) {
                            $imps = $amount * 1000 / app('data')->settings['imps_cost'];
                        }

                        if (0 < $imps) {
                            $q = 'update users set imps = imps + '.$imps.' where id = '.$user_id;
                            db_query($q);
                            continue;
                        }

                        continue;
                    }

                    continue;
                }
            }

            if ($flag == 1) {
                throw new RedirectException($admin_url.'?a=send_bonuce&say=send&total='.$total);
            }
            throw new RedirectException($admin_url.'?a=send_bonuce&say=notsend');
            session(['code' => '']);
            throw new EmptyException();
        }
        throw new RedirectException($admin_url.'?a=send_bonuce&say=invalid_code');
    }

    $code = '';
    if (app('data')->frm['action'] == 'confirm') {
        $account = preg_split('/,/', app('data')->frm['conf_email']);
        $conf_email = array_pop($account);
        app('data')->env['HTTP_HOST'] = preg_replace('/www\\./', '', app('data')->env['HTTP_HOST']);
        $conf_email .= '@'.app('data')->env['HTTP_HOST'];
        $code = get_rand_md5(8);
        send_mail($conf_email, 'Bonus Confirmation Code', 'Code is: '.$code, 'From: '.app('data')->settings['system_email'].' Reply-To: '.app('data')->settings['system_email']);
        $code = get_rand_md5(23).md5($code).get_rand_md5(32);
        session(['code' => $code]);
    }
}

/*
 * @action send_penality_send_penality
 */
if ((app('data')->frm['a'] == 'send_penality' and app('data')->frm['action'] == 'send_penality')) {
    $amount = sprintf('%0.2f', abs(app('data')->frm['amount']));
    if ($amount == 0) {
        throw new RedirectException($admin_url.'?a=send_penality&say=wrongamount');
    }

    $ec = sprintf('%d', app('data')->frm['ec']);
    if (app('data')->frm['to'] == 'user') {
        $q = 'select * from users where username = \''.quote(app('data')->frm['username']).'\'';
    } else {
        if (app('data')->frm['to'] == 'all') {
            $q = 'select * from users where id > 1';
        } else {
            if (app('data')->frm['to'] == 'active') {
                $q = 'select users.* from users, deposits where users.id > 1 and deposits.user_id = users.id group by users.id';
            } else {
                if (app('data')->frm['to'] == 'passive') {
                    $q = 'select u.* from users as u left outer join deposits as d on u.id = d.user_id where u.user_id > 1 and d.user_id is NULL';
                } else {
                    throw new RedirectException($admin_url.'?a=send_penality&say=someerror');
                }
            }
        }
    }

    $sth = db_query($q);
    $flag = 0;
    $total = 0;
    $description = quote(app('data')->frm['description']);
    while ($row = mysql_fetch_array($sth)) {
        $flag = 1;
        $total += $amount;
        $q = 'insert into history set
                user_id = '.$row['id'].(',
                amount = -'.$amount.',
                description = \''.$description.'\',
                type=\'penality\',
                actual_amount = -'.$amount.',
                ec = '.$ec.',
                date = now()');
        db_query($q);
    }

    if ($flag == 1) {
        throw new RedirectException($admin_url.'?a=send_penality&say=send&total='.$total);
    }
    throw new RedirectException($admin_url.'?a=send_penality&say=notsend');
}

/*
 * @action info_box
 */
if ((app('data')->frm['a'] == 'info_box' and app('data')->frm['action'] == 'info_box')) {
    if (app('data')->settings['demomode'] != 1) {
        app('data')->settings['show_info_box'] = sprintf('%d', app('data')->frm['show_info_box']);
        app('data')->settings['show_info_box_started'] = sprintf('%d', app('data')->frm['show_info_box_started']);
        app('data')->settings['show_info_box_running_days'] = sprintf('%d', app('data')->frm['show_info_box_running_days']);
        app('data')->settings['show_info_box_total_accounts'] = sprintf('%d', app('data')->frm['show_info_box_total_accounts']);
        app('data')->settings['show_info_box_active_accounts'] = sprintf('%d', app('data')->frm['show_info_box_active_accounts']);
        app('data')->settings['show_info_box_vip_accounts'] = sprintf('%d', app('data')->frm['show_info_box_vip_accounts']);
        app('data')->settings['vip_users_deposit_amount'] = sprintf('%d', app('data')->frm['vip_users_deposit_amount']);
        app('data')->settings['show_info_box_deposit_funds'] = sprintf('%d', app('data')->frm['show_info_box_deposit_funds']);
        app('data')->settings['show_info_box_today_deposit_funds'] = sprintf('%d', app('data')->frm['show_info_box_today_deposit_funds']);
        app('data')->settings['show_info_box_total_withdraw'] = sprintf('%d', app('data')->frm['show_info_box_total_withdraw']);
        app('data')->settings['show_info_box_visitor_online'] = sprintf('%d', app('data')->frm['show_info_box_visitor_online']);
        app('data')->settings['show_info_box_members_online'] = sprintf('%d', app('data')->frm['show_info_box_members_online']);
        app('data')->settings['show_info_box_newest_member'] = sprintf('%d', app('data')->frm['show_info_box_newest_member']);
        app('data')->settings['show_info_box_last_update'] = sprintf('%d', app('data')->frm['show_info_box_last_update']);
        app('data')->settings['show_kitco_dollar_per_ounce_box'] = sprintf('%d', app('data')->frm['show_kitco_dollar_per_ounce_box']);
        app('data')->settings['show_kitco_euro_per_ounce_box'] = sprintf('%d', app('data')->frm['show_kitco_euro_per_ounce_box']);
        app('data')->settings['show_stats_box'] = sprintf('%d', app('data')->frm['show_stats_box']);
        app('data')->settings['show_members_stats'] = sprintf('%d', app('data')->frm['show_members_stats']);
        app('data')->settings['show_paidout_stats'] = sprintf('%d', app('data')->frm['show_paidout_stats']);
        app('data')->settings['show_top10_stats'] = sprintf('%d', app('data')->frm['show_top10_stats']);
        app('data')->settings['show_last10_stats'] = sprintf('%d', app('data')->frm['show_last10_stats']);
        app('data')->settings['show_refs10_stats'] = sprintf('%d', app('data')->frm['show_refs10_stats']);
        app('data')->settings['refs10_start_date'] = sprintf('%04d-%02d-%02d', substr(app('data')->frm['refs10_start_date'], 0, 4),
            substr(app('data')->frm['refs10_start_date'], 5, 2), substr(app('data')->frm['refs10_start_date'], 8, 2));
        app('data')->settings['show_news_box'] = sprintf('%d', app('data')->frm['show_news_box']);
        app('data')->settings['last_news_count'] = sprintf('%d', app('data')->frm['last_news_count']);
        save_settings();
    }
}

/*
 * @action settings
 */
if ((app('data')->frm['a'] == 'settings' and app('data')->frm['action'] == 'settings')) {
    if (app('data')->settings['demomode'] == 1) {
    } else {
        if (($userinfo['transaction_code'] != '' and $userinfo['transaction_code'] != app('data')->frm['alternative_passphrase'])) {
            throw new RedirectException($admin_url.'?a=settings&say=invalid_passphrase');
        }

        if (app('data')->frm['admin_stat_password'] == '') {
            $q = 'update users set stat_password = \'\' where id = 1';
            db_query($q);
        } else {
            if (app('data')->frm['admin_stat_password'] != '*****') {
                $sp = md5(app('data')->frm['admin_stat_password']);
                $q = 'update users set stat_password = \''.$sp.'\' where id = 1';
                db_query($q);
            }
        }

        app('data')->settings['site_name'] = app('data')->frm['site_name'];
        app('data')->settings['reverse_columns'] = sprintf('%d', app('data')->frm['reverse_columns']);
        app('data')->settings['site_start_day'] = app('data')->frm['site_start_day'];
        app('data')->settings['site_start_month'] = app('data')->frm['site_start_month'];
        app('data')->settings['site_start_year'] = app('data')->frm['site_start_year'];
        app('data')->settings['deny_registration'] = (app('data')->frm['deny_registration'] ? 1 : 0);

        app('data')->settings['use_opt_in'] = sprintf('%d', app('data')->frm['use_opt_in']);
        app('data')->settings['opt_in_email'] = app('data')->frm['opt_in_email'];
        app('data')->settings['system_email'] = app('data')->frm['system_email'];

        app('data')->settings['usercanchangepayeeracc'] = sprintf('%d', app('data')->frm['usercanchangepayeeracc']);
        app('data')->settings['usercanchangeperfectmoneyacc'] = sprintf('%d', app('data')->frm['usercanchangeperfectmoneyacc']);
        app('data')->settings['usercanchangebitcoinacc'] = sprintf('%d', app('data')->frm['usercanchangebitcoinacc']);
        app('data')->settings['usercanchangeemail'] = sprintf('%d', app('data')->frm['usercanchangeemail']);

        app('data')->settings['sendnotify_when_userinfo_changed'] = sprintf('%d', app('data')->frm['sendnotify_when_userinfo_changed']);
        app('data')->settings['graph_validation'] = sprintf('%d', app('data')->frm['graph_validation']);
        app('data')->settings['graph_max_chars'] = app('data')->frm['graph_max_chars'];
        app('data')->settings['graph_text_color'] = app('data')->frm['graph_text_color'];
        app('data')->settings['graph_bg_color'] = app('data')->frm['graph_bg_color'];
        app('data')->settings['advanced_graph_validation'] = (app('data')->frm['advanced_graph_validation'] ? 1 : 0);
        if (! function_exists('imagettfbbox')) {
            app('data')->settings['advanced_graph_validation'] = 0;
        }

        app('data')->settings['advanced_graph_validation_min_font_size'] = sprintf('%d',
            app('data')->frm['advanced_graph_validation_min_font_size']);
        app('data')->settings['advanced_graph_validation_max_font_size'] = sprintf('%d',
            app('data')->frm['advanced_graph_validation_max_font_size']);
        app('data')->settings['enable_calculator'] = app('data')->frm['enable_calculator'];
        app('data')->settings['accesswap'] = sprintf('%d', app('data')->frm['usercanaccesswap']);
        app('data')->settings['time_dif'] = app('data')->frm['time_dif'];
        app('data')->settings['internal_transfer_enabled'] = (app('data')->frm['internal_transfer_enabled'] ? 1 : 0);

        app('data')->settings['brute_force_handler'] = (app('data')->frm['brute_force_handler'] ? 1 : 0);
        app('data')->settings['brute_force_max_tries'] = sprintf('%d', abs(app('data')->frm['brute_force_max_tries']));
        app('data')->settings['redirect_to_https'] = (app('data')->frm['redirect_to_https'] ? 1 : 0);
        app('data')->settings['use_user_location'] = (app('data')->frm['use_user_location'] ? 1 : 0);
        app('data')->settings['use_transaction_code'] = (app('data')->frm['use_transaction_code'] ? 1 : 0);
        app('data')->settings['min_user_password_length'] = sprintf('%d', app('data')->frm['min_user_password_length']);
        app('data')->settings['use_history_balance_mode'] = (app('data')->frm['use_history_balance_mode'] ? 1 : 0);
        app('data')->settings['account_update_confirmation'] = (app('data')->frm['account_update_confirmation'] ? 1 : 0);
        app('data')->settings['withdrawal_fee'] = sprintf('%.02f', app('data')->frm['withdrawal_fee']);
        if (app('data')->settings['withdrawal_fee'] < 0) {
            app('data')->settings['withdrawal_fee'] = '0.00';
        }

        if (100 < app('data')->settings['withdrawal_fee']) {
            app('data')->settings['withdrawal_fee'] = '100.00';
        }

        app('data')->settings['withdrawal_fee_min'] = sprintf('%.02f', app('data')->frm['withdrawal_fee_min']);
        app('data')->settings['min_withdrawal_amount'] = sprintf('%.02f', app('data')->frm['min_withdrawal_amount']);
        app('data')->settings[max_daily_withdraw] = sprintf('%0.2f', app('data')->frm[max_daily_withdraw]);
        app('data')->settings['use_add_funds'] = (app('data')->frm['use_add_funds'] ? 1 : 0);
        $login = quote(app('data')->frm['admin_login']);
        $pass = quote(app('data')->frm['admin_password']);
        $email = quote(app('data')->frm['admin_email']);

        if (($login != '' and $email != '')) {
            $q = 'update users set email=\''.$email.'\', username=\''.$login.'\' where id = 1';
            db_query($q);
        }

        if ($pass != '') {
            $md_pass = md5($pass);
            $q = 'update users set password = \''.$md_pass.'\' where id = 1';
            db_query($q);
        }

        if ((app('data')->frm['use_alternative_passphrase'] == 1 and app('data')->frm['new_alternative_passphrase'] != '')) {
            $altpass = quote(app('data')->frm['new_alternative_passphrase']);
            $q = 'update users set transaction_code = \''.$altpass.'\' where id = 1';
            db_query($q);
        }

        if (app('data')->frm['use_alternative_passphrase'] == 0) {
            $q = 'update users set transaction_code = \'\' where id = 1';
            db_query($q);
        }

        save_settings();
    }

    throw new RedirectException($admin_url.'?a=settings&say=done');
}

/*
 * @action rm_withdraw
 */
if (app('data')->frm['a'] == 'rm_withdraw') {
    $id = sprintf('%d', app('data')->frm['id']);
    $q = 'delete from history where id = '.$id;
    db_query($q);
    throw new RedirectException($admin_url.'?a=thistory&ttype=withdraw_pending');
}

/*
 * @action releasedeposits
 */
if ((app('data')->frm['a'] == 'releasedeposits' and app('data')->frm['action'] == 'releasedeposits')) {
    $u_id = sprintf('%d', app('data')->frm['u_id']);
    $type_ids = app('data')->frm['type_id'];
    while (list($kk, $vv) = each($type_ids)) {
        $kk = intval($kk);
        $vv = intval($vv);
        $q = 'select compound, actual_amount from deposits where id = '.$kk;
        $sth = db_query($q);
        $row = mysql_fetch_array($sth);
        $compound = $row['compound'];
        $amount = $row['actual_amount'];
        $q = 'select * from types where id = '.$vv;
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
                    if (! in_array($compound, $cps)) {
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

        $q = 'update deposits set type_id = '.$vv.', compound = '.$compound.' where id = '.$kk;
        db_query($q);
    }

    $releases = app('data')->frm['release'];
    while (list($kk, $vv) = each($releases)) {
        if ($vv == 0) {
            continue;
        }

        $q = 'select actual_amount, ec from deposits where id = '.$kk;
        $sth = db_query($q);
        if ($row = mysql_fetch_array($sth)) {
            $release_deposit = sprintf('%-.2f', $vv);
            if ($release_deposit <= $row['actual_amount']) {
                $q = 'insert into history set
                        user_id = '.$u_id.',
                        amount = '.$release_deposit.',
                        type = \'early_deposit_release\',
                        actual_amount = '.$release_deposit.',
                        ec = '.$row['ec'].',
                        date = now()';
                db_query($q);
                $dif = floor(($row['actual_amount'] - $release_deposit) * 100) / 100;
                if ($dif == 0) {
                    $q = 'update deposits set actual_amount = 0, amount = 0, status = \'off\' where id = '.$kk;
                } else {
                    $q = 'update deposits set actual_amount = actual_amount - '.$release_deposit.' where id = '.$kk;
                }

                db_query($q);
                continue;
            }

            continue;
        }
    }

    throw new RedirectException($admin_url.'?a=releasedeposits&u_id='.$u_id);
}

/*
 * @action addbonuse_confirm
 */
if ((app('data')->frm['a'] == 'addbonuse' and (app('data')->frm['action'] == 'addbonuse' or app('data')->frm['action'] == 'confirm'))) {
    $deposit = intval(app('data')->frm['deposit']);
    $hyip_id = intval(app('data')->frm['hyip_id']);
    if ($deposit == 1) {
        $q = 'select * from types where id = '.$hyip_id.' and status = \'on\'';
        $sth = db_query($q);
        $type = mysql_fetch_array($sth);
        if (! $type) {
            throw new RedirectException($admin_url.'?a=send_bonuce&say=wrongplan');
        }
    }

    if (app('data')->frm['action'] == 'addbonuse') {
        $code = substr(session('code'), 23, -32);
        if ($code === md5(app('data')->frm['code'])) {
            $id = sprintf('%d', app('data')->frm['id']);
            $amount = sprintf('%f', app('data')->frm['amount']);
            $description = quote(app('data')->frm['desc']);
            $ec = sprintf('%d', app('data')->frm['ec']);
            $q = 'insert into history set
                      user_id = '.$id.',
                      amount = '.$amount.',
                      ec = '.$ec.',
                      actual_amount = '.$amount.',
                      type = \'bonus\',
                      date = now(),
                      description = \''.$description.'\'';
            if (! (db_query($q))) {
            }

            if ($deposit) {
                $delay = $type['delay'] - 1;
                if ($delay < 0) {
                    $delay = 0;
                }

                $user_id = $id;
                $q = 'insert into deposits set
                         user_id = '.$user_id.',
                         type_id = '.$hyip_id.',
                         deposit_date = now(),
                         last_pay_date = now()+ interval '.$delay.' day,
                         status = \'on\',
                         q_pays = 0,
                         amount = \''.$amount.'\',
                         actual_amount = \''.$amount.'\',
                         ec = '.$ec;
                db_query($q);
                $deposit_id = mysql_insert_id();
                $q = 'insert into history set
                        user_id = '.$user_id.',
                        amount = \'-'.$amount.'\',
                        type = \'deposit\',
                        description = \'Deposit to '.quote($type['name']).('\',
                        actual_amount = -'.$amount.',
                        ec = '.$ec.',
                        date = now(),
                        deposit_id = '.$deposit_id);
                db_query($q);
                if (app('data')->settings['banner_extension'] == 1) {
                    $imps = 0;
                    if (0 < app('data')->settings['imps_cost']) {
                        $imps = $amount * 1000 / app('data')->settings['imps_cost'];
                    }

                    if (0 < $imps) {
                        $q = 'update users set imps = imps + '.$imps.' where id = '.$user_id;
                        db_query($q);
                    }
                }
            }

            if (app('data')->frm['inform'] == 1) {
                $q = 'select * from users where id = '.$id;
                $sth = db_query($q);
                $row = mysql_fetch_array($sth);
                $info = [];
                $info['name'] = $row['username'];
                $info['amount'] = number_format($amount, 2);
                send_template_mail('bonus', $row['email'], $info);
            }

            throw new RedirectException($admin_url.'?a=addbonuse&say=done&id='.$id);
        }
        $id = sprintf('%d', app('data')->frm['id']);
        throw new RedirectException($admin_url.'?a=addbonuse&id='.$id.'&say=invalid_code');
    }

    $code = '';
    if (app('data')->frm['action'] == 'confirm') {
        $account = preg_split('/,/', app('data')->frm['conf_email']);
        $conf_email = array_pop($account);
        app('data')->env['HTTP_HOST'] = preg_replace('/www\\./', '', app('data')->env['HTTP_HOST']);
        $conf_email .= '@'.app('data')->env['HTTP_HOST'];
        $code = get_rand_md5(8);
        send_mail($conf_email, 'Bonus Confirmation Code', 'Code is: '.$code, 'From: '.app('data')->settings['system_email'].' Reply-To: '.app('data')->settings['system_email']);
        $code = get_rand_md5(23).md5($code).get_rand_md5(32);
        session(['code' => $code]);
    }
}

/*
 * @action addpenality
 */
if ((app('data')->frm['a'] == 'addpenality' and app('data')->frm['action'] == 'addpenality')) {
    $id = sprintf('%d', app('data')->frm['id']);
    $amount = sprintf('%f', abs(app('data')->frm['amount']));
    $description = quote(app('data')->frm['desc']);
    $ec = sprintf('%d', app('data')->frm['ec']);
    $q = 'insert into history set
            user_id = '.$id.',
            amount = -'.$amount.',
            actual_amount = -'.$amount.',
            ec = '.$ec.',
            type = \'penality\',
            date = now(),
            description = \''.$description.'\'';
    db_query($q);

    if (app('data')->frm['inform'] == 1) {
        $q = 'select * from users where id = '.$id;
        $sth = db_query($q);
        $row = mysql_fetch_array($sth);
        $info = [];
        $info['name'] = $row['username'];
        $info['amount'] = number_format($amount, 2);
        send_template_mail('penalty', $row['email'], $info);
    }

    throw new RedirectException($admin_url.'?a=addpenality&say=done&id='.$id);
}

/*
 * @action deleteaccount
 */
if (app('data')->frm['a'] == 'deleteaccount') {
    $id = sprintf('%d', app('data')->frm['id']);
    $q = 'delete from users where id = '.$id.' and id <> 1';
    db_query($q);
    throw new RedirectException($admin_url.'?a=members&q='.app('data')->frm['q'].'&p='.app('data')->frm['p'].'&status='.app('data')->frm['status']);
}

/*
 * @action editaccount
 */
if ((app('data')->frm['a'] == 'editaccount' and app('data')->frm['action'] == 'editaccount')) {
    $id = sprintf('%d', app('data')->frm['id']);
    if (((app('data')->settings['demomode'] == 1 and $id <= 3) and 0 < $id)) {
        throw new RedirectException($admin_url.'?a=editaccount&id='.app('data')->frm['id']);
    }

    $username = quote(app('data')->frm['username']);
    $q = 'select * from users where id <> '.$id.' and username = \''.$username.'\'';
    $sth = db_query($q);
    $row = mysql_fetch_array($sth);
    if ($row) {
        throw new RedirectException($admin_url.'?a=editaccount&say=userexists&id='.app('data')->frm['id']);
    }

    if ((app('data')->frm['password'] != '' and app('data')->frm['password'] != app('data')->frm['password2'])) {
        throw new RedirectException($admin_url.'?a=editaccount&say=incorrect_password&id='.app('data')->frm['id']);
    }

    if ((app('data')->frm['transaction_code'] != '' and app('data')->frm['transaction_code'] != app('data')->frm['transaction_code2'])) {
        throw new RedirectException($admin_url.'?a=editaccount&say=incorrect_transaction_code&id='.app('data')->frm['id']);
    }

    if ($id == 0) {
        $name = quote(app('data')->frm['fullname']);
        $username = quote(app('data')->frm['username']);
        $password = md5(quote(app('data')->frm['password']));
        $perfectmoney = quote(app('data')->frm['perfectmoney']);
        $payeer = quote(app('data')->frm['payeer']);
        $bitcoin = quote(app('data')->frm['bitcoin']);
        $email = quote(app('data')->frm['email']);
        $status = quote(app('data')->frm['status']);
        $auto_withdraw = sprintf('%d', app('data')->frm['auto_withdraw']);
        $admin_auto_pay_earning = sprintf('%d', app('data')->frm['admin_auto_pay_earning']);
        $pswd = '';
        if (app('data')->settings['store_uncrypted_password'] == 1) {
            $pswd = quote(app('data')->frm['password']);
        }

        $q = 'insert into users set
                name = \''.$name.'\',
                username = \''.$username.'\',
                password = \''.$password.'\',
                perfectmoney_account = \''.$perfectmoney.'\',
                payeer_account = \''.$payeer.'\',
                bitcoin_account = \''.$bitcoin.'\',
                email = \''.$email.'\',
                status = \''.$status.'\',
                auto_withdraw = '.$auto_withdraw.',
                admin_auto_pay_earning = '.$admin_auto_pay_earning.',
                user_auto_pay_earning = '.$admin_auto_pay_earning.',
                pswd = \''.$pswd.'\',
                date_register = now()';
        db_query($q);
        app('data')->frm['id'] = mysql_insert_id();
    } else {
        $q = 'select * from users where id = '.$id;
        $sth = db_query($q);
        ($row = mysql_fetch_array($sth));
        $name = quote(app('data')->frm['fullname']);
        $address = quote(app('data')->frm['address']);
        $city = quote(app('data')->frm['city']);
        $state = quote(app('data')->frm['state']);
        $zip = quote(app('data')->frm['zip']);
        $country = quote(app('data')->frm['country']);
        $edit_location = '';
        if (app('data')->settings['use_user_location']) {
            $edit_location = 'address = \''.$address.'\',
                city = \''.$city.'\',
                state = \''.$state.'\',
                zip = \''.$zip.'\',
                country = \''.$country.'\',';
        }

        $username = quote(app('data')->frm['username']);
        $password = quote(app('data')->frm['password']);
        $transaction_code = quote(app('data')->frm['transaction_code']);
        $perfectmoney = quote(app('data')->frm['perfectmoney']);
        $payeer = quote(app('data')->frm['payeer']);
        $bitcoin = quote(app('data')->frm['bitcoin']);
        $email = quote(app('data')->frm['email']);
        $status = quote(app('data')->frm['status']);
        $auto_withdraw = sprintf('%d', app('data')->frm['auto_withdraw']);
        $admin_auto_pay_earning = sprintf('%d', app('data')->frm['admin_auto_pay_earning']);
        $user_auto_pay_earning = $row['user_auto_pay_earning'];
        if (($row['admin_auto_pay_earning'] == 0 and $admin_auto_pay_earning == 1)) {
            $user_auto_pay_earning = 1;
        }

        $q = 'update users set
                name = \''.$name.'\',
                '.$edit_location.'
                username = \''.$username.'\',
                perfectmoney_account = \''.$perfectmoney.'\',
                payeer_account = \''.$payeer.'\',
                bitcoin_account = \''.$bitcoin.'\',
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
            $q = 'update users set password = \''.$password.'\' where id = '.$id.' and id <> 1';
            db_query($q);
            if (app('data')->settings['store_uncrypted_password'] == 1) {
                $q = 'update users set pswd = \''.$pswd.'\' where id = '.$id.' and id <> 1';
                db_query($q);
            }
        }

        if ($transaction_code != '') {
            $pswd = quote($password);
            $password = md5($password);
            $q = 'update users set transaction_code = \''.$transaction_code.'\' where id = '.$id.' and id <> 1';
            db_query($q);
        }

        if (app('data')->frm['activate']) {
            $q = 'update users set activation_code = \'\', bf_counter = 0 where id = '.$id.' and id <> 1';
            db_query($q);
        }
    }

    throw new RedirectException($admin_url.'?a=editaccount&id='.app('data')->frm['id'].'&say=saved');
}

/*
 * @action members_modify_status
 */
if ((app('data')->frm['a'] == 'members' and app('data')->frm['action'] == 'modify_status')) {
    if (app('data')->settings['demomode'] != 1) {
        $active = app('data')->frm['active'];
        while (list($id, $status) = each($active)) {
            $qstatus = quote($status);
            $q = 'update users set status = \''.$qstatus.'\' where id = '.$id;
            db_query($q);
        }
    }

    throw new RedirectException($admin_url.'?a=members');
}

/*
 * @action members_activate
 */
if ((app('data')->frm['a'] == 'members' and app('data')->frm['action'] == 'activate')) {
    $active = app('data')->frm['activate'];
    while (list($id, $status) = each($active)) {
        $q = 'update users set activation_code = \'\', bf_counter = 0 where id = '.$id;
        db_query($q);
    }

    throw new RedirectException($admin_url.'?a=members&status=blocked');
}

/*
 * @action add_hyip
 */
if (app('data')->frm['action'] == 'add_hyip') {
    $q_days = sprintf('%d', app('data')->frm['hq_days']);
    if (app('data')->frm['hq_days_nolimit'] == 1) {
        $q_days = 0;
    }

    $min_deposit = sprintf('%0.2f', app('data')->frm['hmin_deposit']);
    $max_deposit = sprintf('%0.2f', app('data')->frm['hmax_deposit']);
    $return_profit = sprintf('%d', app('data')->frm['hreturn_profit']);
    $return_profit_percent = sprintf('%d', app('data')->frm['hreturn_profit_percent']);
    $percent = sprintf('%0.2f', app('data')->frm['hpercent']);
    $use_compound = sprintf('%d', app('data')->frm['use_compound']);
    $work_week = sprintf('%d', app('data')->frm['work_week']);
    $parent = sprintf('%d', app('data')->frm['parent']);
    $desc = quote(app('data')->frm[plan_description]);
    $withdraw_principal = sprintf('%d', app('data')->frm['withdraw_principal']);
    $withdraw_principal_percent = sprintf('%.02f', app('data')->frm['withdraw_principal_percent']);
    $withdraw_principal_duration = sprintf('%d', app('data')->frm['withdraw_principal_duration']);
    $withdraw_principal_duration_max = sprintf('%d', app('data')->frm['withdraw_principal_duration_max']);
    $compound_min_deposit = sprintf('%.02f', app('data')->frm['compound_min_deposit']);
    $compound_max_deposit = sprintf('%.02f', app('data')->frm['compound_max_deposit']);
    $compound_percents_type = sprintf('%d', app('data')->frm['compound_percents_type']);
    $compound_min_percent = sprintf('%.02f', app('data')->frm['compound_min_percent']);
    if (($compound_min_percent < 0 or 100 < $compound_min_percent)) {
        $compound_min_percent = 0;
    }

    $compound_max_percent = sprintf('%.02f', app('data')->frm['compound_max_percent']);
    if (($compound_max_percent < 0 or 100 < $compound_max_percent)) {
        $compound_max_percent = 100;
    }

    $cps = preg_split('/\\s*,\\s*/', app('data')->frm['compound_percents']);
    $cps1 = [];
    foreach ($cps as $cp) {
        if (((! in_array($cp, $cps1) and 0 <= $cp) and $cp <= 100)) {
            array_push($cps1, sprintf('%d', $cp));
            continue;
        }
    }

    sort($cps1);
    $compound_percents = implode(',', $cps1);
    $hold = sprintf('%d', app('data')->frm[hold]);
    $delay = sprintf('%d', app('data')->frm[delay]);
    $q = 'insert into types set
            name=\''.quote(app('data')->frm['hname']).('\',
            q_days = '.$q_days.',
            period = \'').quote(app('data')->frm['hperiod']).'\',
            status = \''.quote(app('data')->frm['hstatus']).('\',
            return_profit = \''.$return_profit.'\',
            return_profit_percent = '.$return_profit_percent.',
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
            delay = '.$delay);
    if (! (db_query($q))) {
    }

    $parent = mysql_insert_id();
    $rate_amount_active = app('data')->frm['rate_amount_active'];
    for ($i = 0; $i < 300; ++$i) {
        if (app('data')->frm['rate_amount_active'][$i] == 1) {
            $name = quote(app('data')->frm['rate_amount_name'][$i]);
            $min_amount = sprintf('%0.2f', app('data')->frm['rate_min_amount'][$i]);
            $max_amount = sprintf('%0.2f', app('data')->frm['rate_max_amount'][$i]);
            $percent = sprintf('%0.2f', app('data')->frm['rate_percent'][$i]);
            $q = 'insert into plans set
                    parent='.$parent.',
                    name=\''.$name.'\',
                    min_deposit = '.$min_amount.',
                    max_deposit = '.$max_amount.',
                    percent = '.$percent;
            if (! (db_query($q))) {
            }

            continue;
        }
    }

    throw new RedirectException($admin_url.'?a=rates');
}

/*
 * @action edit_hyip
 */
if (app('data')->frm['action'] == 'edit_hyip') {
    $id = sprintf('%d', app('data')->frm['hyip_id']);
    if (($id < 3 and app('data')->settings['demomode'] == 1)) {
        throw new RedirectException($admin_url.'?a=rates');
    }

    $q_days = sprintf('%d', app('data')->frm['hq_days']);
    if (app('data')->frm['hq_days_nolimit'] == 1) {
        $q_days = 0;
    }

    $min_deposit = sprintf('%0.2f', app('data')->frm['hmin_deposit']);
    $max_deposit = sprintf('%0.2f', app('data')->frm['hmax_deposit']);
    $return_profit = sprintf('%d', app('data')->frm['hreturn_profit']);
    $return_profit_percent = sprintf('%d', app('data')->frm['hreturn_profit_percent']);
    $percent = sprintf('%0.2f', app('data')->frm['hpercent']);
    $work_week = sprintf('%d', app('data')->frm['work_week']);
    $use_compound = sprintf('%d', app('data')->frm['use_compound']);
    $parent = sprintf('%d', app('data')->frm['parent']);
    $desc = quote(app('data')->frm[plan_description]);
    $withdraw_principal = sprintf('%d', app('data')->frm['withdraw_principal']);
    $withdraw_principal_percent = sprintf('%.02f', app('data')->frm['withdraw_principal_percent']);
    $withdraw_principal_duration = sprintf('%d', app('data')->frm['withdraw_principal_duration']);
    $withdraw_principal_duration_max = sprintf('%d', app('data')->frm['withdraw_principal_duration_max']);
    $compound_min_deposit = sprintf('%.02f', app('data')->frm['compound_min_deposit']);
    $compound_max_deposit = sprintf('%.02f', app('data')->frm['compound_max_deposit']);
    $compound_percents_type = sprintf('%d', app('data')->frm['compound_percents_type']);
    $compound_min_percent = sprintf('%.02f', app('data')->frm['compound_min_percent']);
    if (($compound_min_percent < 0 or 100 < $compound_min_percent)) {
        $compound_min_percent = 0;
    }

    $compound_max_percent = sprintf('%.02f', app('data')->frm['compound_max_percent']);
    if (($compound_max_percent < 0 or 100 < $compound_max_percent)) {
        $compound_max_percent = 100;
    }

    $cps = preg_split('/\\s*,\\s*/', app('data')->frm['compound_percents']);
    $cps1 = [];
    foreach ($cps as $cp) {
        if (((! in_array($cp, $cps1) and 0 <= $cp) and $cp <= 100)) {
            array_push($cps1, sprintf('%d', $cp));
            continue;
        }
    }

    sort($cps1);
    $compound_percents = implode(',', $cps1);
    $closed = (app('data')->frm['closed'] ? 1 : 0);
    $hold = sprintf('%d', app('data')->frm[hold]);
    $delay = sprintf('%d', app('data')->frm[delay]);
    $q = 'update types set
            name=\''.quote(app('data')->frm['hname']).('\',
            q_days = '.$q_days.',
            period = \'').quote(app('data')->frm['hperiod']).'\',
            status = \''.quote(app('data')->frm['hstatus']).('\',
            return_profit = \''.$return_profit.'\',
            return_profit_percent = '.$return_profit_percent.',
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
            where id='.$id);
    db_query($q);

    $parent = $id;
    $q = 'delete from plans where parent = '.$id;
    if (! (db_query($q))) {
    }

    $rate_amount_active = app('data')->frm['rate_amount_active'];
    for ($i = 0; $i < 300; ++$i) {
        if (app('data')->frm['rate_amount_active'][$i] == 1) {
            $name = quote(app('data')->frm['rate_amount_name'][$i]);
            $min_amount = sprintf('%0.2f', app('data')->frm['rate_min_amount'][$i]);
            $max_amount = sprintf('%0.2f', app('data')->frm['rate_max_amount'][$i]);
            $percent = sprintf('%0.2f', app('data')->frm['rate_percent'][$i]);
            $q = 'insert into plans set
                    parent='.$parent.',
                    name=\''.$name.'\',
                    min_deposit = '.$min_amount.',
                    max_deposit = '.$max_amount.',
                    percent = '.$percent;
        db_query($q);

            continue;
        }
    }

    throw new RedirectException($admin_url.'?a=rates');
}

/*
 * @action thistory_download_csv
 */
if ((app('data')->frm['a'] == 'thistory' and app('data')->frm['action2'] == 'download_csv')) {
    app('data')->frm['day_to'] = sprintf('%d', app('data')->frm['day_to']);
    app('data')->frm['month_to'] = sprintf('%d', app('data')->frm['month_to']);
    app('data')->frm['year_to'] = sprintf('%d', app('data')->frm['year_to']);
    app('data')->frm['day_from'] = sprintf('%d', app('data')->frm['day_from']);
    app('data')->frm['month_from'] = sprintf('%d', app('data')->frm['month_from']);
    app('data')->frm['year_from'] = sprintf('%d', app('data')->frm['year_from']);
    if (app('data')->frm['day_to'] == 0) {
        app('data')->frm['day_to'] = date('j', time() + app('data')->settings['time_dif'] * 60 * 60);
        app('data')->frm['month_to'] = date('n', time() + app('data')->settings['time_dif'] * 60 * 60);
        app('data')->frm['year_to'] = date('Y', time() + app('data')->settings['time_dif'] * 60 * 60);
        app('data')->frm['day_from'] = 1;
        app('data')->frm['month_from'] = app('data')->frm['month_to'];
        app('data')->frm['year_from'] = app('data')->frm['year_to'];
    }

    $datewhere = '\''.app('data')->frm['year_from'].'-'.app('data')->frm['month_from'].'-'.app('data')->frm['day_from'].'\' + interval 0 day < date + interval '.app('data')->settings['time_dif'].' hour and '.'\''.app('data')->frm['year_to'].'-'.app('data')->frm['month_to'].'-'.app('data')->frm['day_to'].'\' + interval 1 day > date + interval '.app('data')->settings['time_dif'].' hour ';
    if (app('data')->frm['ttype'] != '') {
        if (app('data')->frm['ttype'] == 'exchange') {
            $typewhere = ' and (type=\'exchange_out\' or type=\'exchange_in\')';
        } else {
            $typewhere = ' and type=\''.quote(app('data')->frm['ttype']).'\' ';
        }
    }

    $u_id = sprintf('%d', app('data')->frm['u_id']);
    if (1 < $u_id) {
        $userwhere = ' and user_id = '.$u_id.' ';
    }

    $ecwhere = '';
    if (app('data')->frm[ec] == '') {
        app('data')->frm[ec] = -1;
    }

    $ec = sprintf('%d', app('data')->frm[ec]);
    if (-1 < app('data')->frm[ec]) {
        $ecwhere = ' and ec = '.$ec;
    }

    $q = 'select *, date_format(date + interval '.app('data')->settings['time_dif'].(' hour, \'%b-%e-%Y %r\') as d from history where '.$datewhere.' '.$userwhere.' '.$typewhere.' '.$ecwhere.' order by date desc, id desc');
    $sth = db_query($q);
    $trans = [];
    while ($row = mysql_fetch_array($sth)) {
        $q = 'select username from users where id = '.$row['user_id'];
        $sth1 = db_query($q);
        $row1 = mysql_fetch_array($sth1);
        if ($row1) {
            $row['username'] = $row1['username'];
        } else {
            $row['username'] = '-- deleted user --';
        }

        array_push($trans, $row);
    }

    $from = app('data')->frm['month_from'].'_'.app('data')->frm['day_from'].'_'.app('data')->frm['year_from'];
    $to = app('data')->frm['month_to'].'_'.app('data')->frm['day_to'].'_'.app('data')->frm['year_to'];
    header('Content-Disposition: attachment; filename='.app('data')->frm['ttype'].('history-'.$from.'-'.$to.'.csv'));
    header('Content-type: text/coma-separated-values');
    echo '"Transaction Type","User","Amount","Currency","Date","Description"';
    for ($i = 0; $i < count($trans); ++$i) {
        echo '"'.config('hm.transtype')[$trans[$i]['type']].'","'.$trans[$i]['username'].'","$'.number_format(abs($trans[$i]['actual_amount']),
                2).'","'.app('data')->exchange_systems[$trans[$i]['ec']]['name'].'","'.$trans[$i]['d'].'","'.$trans[$i]['description'].'"';
    }
}

/*
 * @action add_processing
 */
if ((app('data')->frm['a'] == 'add_processing' and app('data')->frm[action] == 'add_processing')) {
    if (! app('data')->settings['demomode']) {
        $status = (app('data')->frm['status'] ? 1 : 0);
        $name = quote(app('data')->frm['name']);
        $description = quote(app('data')->frm['description']);
        $use = app('data')->frm['field'];
        $fields = [];
        if ($use) {
            reset($use);
            $i = 1;
            foreach ($use as $id => $value) {
                if (app('data')->frm['use'][$id]) {
                    $fields[$i] = $value;
                    ++$i;
                    continue;
                }
            }
        }

        $qfields = serialize($fields);
        $q = 'select max(id) as max_id from processings';
        $sth = db_query($q);
        $row = mysql_fetch_array($sth);
        $max_id = $row['max_id'];
        if ($max_id < 999) {
            $max_id = 998;
        }

        ++$max_id;
        $q = 'insert into processings set
                 id = '.$max_id.',
                 status = '.$status.',
                 name = \''.$name.'\',
                 description = \''.$description.'\',
                 infofields = \''.quote($qfields).'\'';
        db_query($q);
    }

    throw new RedirectException($admin_url.'?a=processings');
}

/*
 * @action edit_processing
 */
if ((app('data')->frm['a'] == 'edit_processing' and app('data')->frm[action] == 'edit_processing')) {
    if (! app('data')->settings['demomode']) {
        $pid = intval(app('data')->frm['pid']);
        $status = (app('data')->frm['status'] ? 1 : 0);
        $name = quote(app('data')->frm['name']);
        $description = quote(app('data')->frm['description']);
        $use = app('data')->frm['field'];
        $fields = [];
        if ($use) {
            reset($use);
            $i = 1;
            foreach ($use as $id => $value) {
                if (app('data')->frm['use'][$id]) {
                    $fields[$i] = $value;
                    ++$i;
                    continue;
                }
            }
        }

        $qfields = serialize($fields);
        $q = 'update processings set
                status = '.$status.',
                name = \''.$name.'\',
                description = \''.$description.'\',
                infofields = \''.quote($qfields).'\'
                where id = '.$pid;
        db_query($q);
    }

    throw new RedirectException($admin_url.'?a=processings');
}

/*
 * @action update_processings
 */
if (app('data')->frm['a'] == 'update_processings') {
    if (! app('data')->settings['demomode']) {
        $q = 'update processings set status = 0';
        db_query($q);
        $status = app('data')->frm['status'];
        if ($status) {
            foreach ($status as $id => $v) {
                $q = 'update processings set status = 1 where id = '.$id;
                db_query($q);
            }
        }
    }

    throw new RedirectException($admin_url.'?a=processings');
}

/*
 * @action delete_processing
 */
if (app('data')->frm['a'] == 'delete_processing') {
    if (! app('data')->settings['demomode']) {
        $pid = intval(app('data')->frm['pid']);
        $q = 'delete from processings where id = '.$pid;
        db_query($q);
    }

    throw new RedirectException($admin_url.'?a=processings');
}

include app_path('Hm').'/inc/admin/html.header.php';
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
include app_path('Hm').'/inc/admin/menu.php';
echo '                 <br>

              </td>
              <td bgcolor="#ff8d00" valign="top" width=1><img src=images/q.gif width=1 height=1></td>
              <td bgcolor="#FFFFFF" valign="top" width=99%>
            <!-- Main: Start -->
            <table width="100%" height="100%" border="0" cellpadding="10" cellspacing="0" class="forTexts">
              <tr>
                <td width=100% height';
echo '=100% valign=top>';

switch (app('data')->frm['a']) {
    case 'rates':
        include app_path('Hm').'/inc/admin/rates.php';
        break;
    case 'editrate':
        include app_path('Hm').'/inc/admin/edit_hyip.php';
        break;
    case 'add_hyip':
        include app_path('Hm').'/inc/admin/add_hyip.php';
        break;
    case 'members':
        include app_path('Hm').'/inc/admin/members.php';
        break;
    case 'editaccount':
        include app_path('Hm').'/inc/admin/editaccount.php';
        break;
    case 'addmember':
        include app_path('Hm').'/inc/admin/addmember.php';
        break;
    case 'userexists':
        include app_path('Hm').'/inc/admin/error_userexists.php';
        break;
    case 'userfunds':
        include app_path('Hm').'/inc/admin/manage_user_funds.php';
        break;
    case 'addbonuse':
        include app_path('Hm').'/inc/admin/addbonuse.php';
        break;
    case 'mass':
        include app_path('Hm').'/inc/admin/prepare_mass_pay.php';
        break;
    case 'thistory':
        include app_path('Hm').'/inc/admin/transactions_history.php';
        break;
    case 'addpenality':
        include app_path('Hm').'/inc/admin/addpenality.php';
        break;
    case 'releasedeposits':
        include app_path('Hm').'/inc/admin/releaseusersdeposits.php';
        break;
    case 'pay_withdraw':
        include app_path('Hm').'/inc/admin/process_withdraw.php';
        break;
    case 'settings':
        include app_path('Hm').'/inc/admin/settings.php';
        break;
    case 'info_box':
        include app_path('Hm').'/inc/admin/info_box_settings.php';
        break;
    case 'send_bonuce':
        include app_path('Hm').'/inc/admin/send_bonuce.php';
        break;
    case 'send_penality':
        include app_path('Hm').'/inc/admin/send_penality.php';
        break;
    case 'newsletter':
        include app_path('Hm').'/inc/admin/newsletter.php';
        break;
    case 'referal':
        include app_path('Hm').'/inc/admin/referal.php';
        break;
    case 'error_pay_log':
        include app_path('Hm').'/inc/admin/error_pay_log.php';
        break;
    case 'news':
        include app_path('Hm').'/inc/admin/news.php';
        break;
    case 'affilates':
        include app_path('Hm').'/inc/admin/affilates.php';
        break;
    case 'custompages':
        include app_path('Hm').'/inc/admin/custompage.php';
        break;
    case 'exchange_rates':
        include app_path('Hm').'/inc/admin/exchange_rates.php';
        break;
    case 'security':
        include app_path('Hm').'/inc/admin/security.php';
        break;
    case 'processings':
        include app_path('Hm').'/inc/admin/processings.php';
        break;
    case 'add_processing':
        include app_path('Hm').'/inc/admin/add_processing.php';
        break;
    case 'edit_processing':
        include app_path('Hm').'/inc/admin/edit_processing.php';
        break;
    case 'pending_deposits':
        include app_path('Hm').'/inc/admin/pending_deposits.php';
        break;
    case 'pending_deposit_details':
        include app_path('Hm').'/inc/admin/pending_deposit_details.php';
        break;
    case 'startup_bonus':
        include app_path('Hm').'/inc/admin/startup_bonus.php';
        break;
    default:
        include app_path('Hm').'/inc/admin/main.php';
}
echo '</td></tr></table><!-- Main: END --></td></tr></table></td></tr></table></td></tr>';
include app_path('Hm').'/inc/admin/html.footer.php';
