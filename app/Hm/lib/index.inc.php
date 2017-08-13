<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

function bind_ref()
{
    global $frm;
    global $frm_cookie;
    global $settings;
    setcookie('Referer', $frm['ref'], time() + 630720000);
    if ($frm_cookie['Referer'] == '') {
        $ref = quote($frm['ref']);
        $q = 'select id from hm2_users where username = \''.$ref.'\'';
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            $ref_id = $row['id'];
            $q = 'select * from hm2_referal_stats where date = current_date() and user_id = '.$ref_id;
            $sth = db_query($q);
            $f = 0;
            while ($row = mysql_fetch_array($sth)) {
                $f = 1;
            }

            if ($f == 0) {
                $q = 'insert into hm2_referal_stats set date = current_date(), user_id = '.$ref_id.', income = 1, reg = 0';
                db_query($q);
            } else {
                $q = 'update hm2_referal_stats set income = income+1 where date = current_date() and user_id = '.$ref_id.' ';
                db_query($q);
            }

            break;
        }
    }

    if ($settings['redirect_referrals'] != '') {
        header('Location: '.$settings['redirect_referrals']);
        exit;
    }
}

function redirect_https()
{
    global $frm_env;
    global $env_frm;
    $url = 'https://'.$frm_env['HTTP_HOST'].$frm_env['SCRIPT_NAME'];
    if ($env_frm['QUERY_STRING']) {
        $url .= $env_frm['QUERY_STRING'];
    }

    header('Location: '.$url);
}

function custom2_pay_withdraw_eeecurrency()
{
    global $frm;
    global $exchange_systems;
    global $settings;
    $batch = $frm['TRANSACTION_ID'];
    list($id, $str) = explode('-', $frm['CUSTOM1']);
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
            amount = -'.abs($row['amount']).(',
            type = \'withdrawal\',
            description = \'Withdraw processed. Batch id = '.$batch.'\',
            actual_amount = -').abs($row['amount']).',
            ec = 8,
            date = now()';
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
        $info['currency'] = $exchange_systems[8]['name'];
        send_template_mail('withdraw_user_notification', $userinfo['email'], $settings['system_email'], $info);
    }
}

function custom2_pay_withdraw()
{
    global $frm;
    global $exchange_systems;
    global $settings;
    $batch = $frm['TRANSACTION_ID'];
    list($id, $str) = explode('-', $frm['CUSTOM1']);
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
            amount = -'.abs($row['amount']).(',
            type = \'withdrawal\',
            description = \'Withdraw processed. Batch id = '.$batch.'\',
            actual_amount = -').abs($row['amount']).',
            ec = 2,
            date = now()';
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
}

function user3_pay_withdraw_payment()
{
    global $frm;
    global $exchange_systems;
    global $settings;
    $batch = $frm['transaction_id'];
    list($id, $str) = explode('-', $frm['user1']);
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
            amount = -'.abs($row['amount']).(',
            type = \'withdrawal\',
            description = \'Withdraw processed. Batch id = '.$batch.'\',
            actual_amount = -').abs($row['amount']).',
            ec = 4,
            date = now()';
        db_query($q);
        $q = 'select * from hm2_users where id = '.$row['user_id'];
        $sth = db_query($q);
        $userinfo = mysql_fetch_array($sth);
        $info = [];
        $info['username'] = $userinfo['username'];
        $info['name'] = $userinfo['name'];
        $info['amount'] = sprintf('%.02f', abs($row['amount']));
        $info['account'] = $frm['payee_email'];
        $info['batch'] = $batch;
        $info['paying_batch'] = $batch;
        $info['receiving_batch'] = $batch;
        $info['currency'] = $exchange_systems[4]['name'];
        send_template_mail('withdraw_user_notification', $userinfo['email'], $settings['system_email'], $info);
    }
}

function show_info_box()
{
    global $settings;
    global $stats;
    if ($settings['show_info_box_members_online'] == 1) {
        if ($settings['crontab_stats'] == 1) {
            $settings['show_info_box_members_online_generated'] = $stats['visitors'];
        } else {
            $q = 'select count(*) as col from hm2_users where last_access_time + interval 30 minute > now()';
            $sth = db_query($q);
            $row = mysql_fetch_array($sth);
            $settings['show_info_box_members_online_generated'] = $row['col'];
        }
    }

    if ($settings['show_info_box_total_accounts'] == 1) {
        if ($settings['crontab_stats'] == 1) {
            $settings['info_box_total_accounts_generated'] = $stats['total_users'];
        } else {
            $q = 'select count(*) as col from hm2_users where id > 1';
            $sth = db_query($q);
            $row = mysql_fetch_array($sth);
            $settings['info_box_total_accounts_generated'] = $row['col'];
        }
    }

    if ($settings['show_info_box_active_accounts'] == 1) {
        if ($settings['crontab_stats'] == 1) {
            $settings['info_box_total_active_accounts_generated'] = $stats['active_accounts'];
        } else {
            $q = 'select count(distinct user_id) as col from hm2_deposits ';
            $sth = db_query($q);
            $row = mysql_fetch_array($sth);
            $settings['info_box_total_active_accounts_generated'] = $row['col'];
        }
    }

    if ($settings['show_info_box_vip_accounts'] == 1) {
        $q = 'select count(distinct user_id) as col from hm2_deposits where actual_amount > '.sprintf('%.02f',
                $settings['vip_users_deposit_amount']);
        $sth = db_query($q);
        $row = mysql_fetch_array($sth);
        $settings['info_box_total_vip_accounts_generated'] = $row['col'];
    }

    if ($settings['show_info_box_deposit_funds'] == 1) {
        if ($settings['crontab_stats'] == 1) {
            $settings['info_box_deposit_funds_generated'] = number_format($stats['total_deposited'], 2);
        } else {
            $q = 'select sum(amount) as sum from hm2_deposits';
            $sth = db_query($q);
            $row = mysql_fetch_array($sth);
            $settings['info_box_deposit_funds_generated'] = number_format($row['sum'], 2);
        }
    }

    if ($settings['show_info_box_today_deposit_funds'] == 1) {
        $q = 'select sum(amount) as sum from hm2_deposits where to_days(deposit_date) = to_days(now() + interval '.$settings['time_dif'].' day)';
        $sth = db_query($q);
        $row = mysql_fetch_array($sth);
        $settings['info_box_today_deposit_funds_generated'] = number_format($row['sum'], 2);
    }

    if ($settings['show_info_box_total_withdraw'] == 1) {
        if ($settings['crontab_stats'] == 1) {
            $settings['info_box_withdraw_funds_generated'] = number_format(abs($stats['total_withdraw']), 2);
        } else {
            $q = 'select sum(amount) as sum from hm2_history where type=\'withdrawal\'';
            $sth = db_query($q);
            $row = mysql_fetch_array($sth);
            $settings['info_box_withdraw_funds_generated'] = number_format(abs($row['sum']), 2);
        }
    }

    if ($settings['show_info_box_visitor_online'] == 1) {
        $q = 'select count(*) as sum from hm2_online';
        $sth = db_query($q);
        $row = mysql_fetch_array($sth);
        $settings['info_box_visitor_online_generated'] = $row['sum'];
    }

    if ($settings['show_info_box_newest_member'] == 1) {
        $q = 'select username from hm2_users where status = \'on\' order by id desc limit 0,1';
        $sth = db_query($q);
        $row = mysql_fetch_array($sth);
        $settings['show_info_box_newest_member_generated'] = $row['username'];
    }

    if ($settings['show_info_box_last_update'] == 1) {
        $settings['show_info_box_last_update_generated'] = date('M j, Y', time() + $settings['time_dif'] * 60 * 60);
    }
}

function do_login(&$userinfo)
{
    global $frm;
    global $settings;
    global $frm_env;
    $username = quote($frm['username']);
    $password = quote($frm['password']);
    $password = md5($password);
    $add_opt_in_check = '';
    if ($settings['use_opt_in'] == 1) {
        $add_opt_in_check = ' and (confirm_string = "" or confirm_string is NULL)';
    }

    $q = 'select *, date_format(date_register, \'%b-%e-%Y\') as create_account_date, now() - interval 2 minute > l_e_t as should_count from hm2_users where username = \''.$username.'\' and (status=\'on\' or status=\'suspended\') '.$add_opt_in_check;
    $sth = db_query($q);
    while ($row = mysql_fetch_array($sth)) {
        if (((extension_loaded('gd') and $settings['graph_validation'] == 1) and 0 < $settings['graph_max_chars'])) {
            session_start();
            if ($_SESSION['validation_number'] != $frm['validation_number']) {
                header('Location: ?a=login&say=invalid_login&username='.$frm['username']);
                exit;
            }
        }

        if (($settings['brute_force_handler'] == 1 and $row['activation_code'] != '')) {
            header('Location: ?a=login&say=invalid_login&username='.$frm['username']);
            exit;
        }

        if (($settings['brute_force_handler'] == 1 and $row['bf_counter'] == $settings['brute_force_max_tries'])) {
            $activation_code = get_rand_md5(50);
            $q = 'update hm2_users set bf_counter = bf_counter + 1, activation_code = \''.$activation_code.'\' where id = '.$row['id'];
            db_query($q);
            $info = [];
            $info['activation_code'] = $activation_code;
            $info['username'] = $row['username'];
            $info['name'] = $row['name'];
            $info['ip'] = $frm_env['REMOTE_ADDR'];
            $info['max_tries'] = $settings['brute_force_max_tries'];
            send_template_mail('brute_force_activation', $row['email'], $settings['system_email'], $info);
            header('Location: ?a=login&say=invalid_login&username='.$frm['username']);
            exit;
        }
        if ($row['password'] != $password) {
            $q = 'update hm2_users set bf_counter = bf_counter + 1 where id = '.$row['id'];
            db_query($q);
            header('Location: ?a=login&say=invalid_login&username='.$frm['username']);
            exit;
        }

        $hid = get_rand_md5(20);
        $qhid = get_rand_md5(5).$hid.get_rand_md5(5);
        $chid = $row['id'].'-'.md5($hid);
        $userinfo = $row;
        $userinfo['logged'] = 1;
        $ip = $frm_env['REMOTE_ADDR'];
        $q = 'update hm2_users set hid = \''.$qhid.'\', bf_counter = 0, last_access_time = now(), last_access_ip = \''.$ip.'\' where id = '.$row['id'];
        db_query($q);
        $q = 'insert into hm2_user_access_log set user_id = '.$userinfo['id'].(', date = now(), ip = \''.$ip.'\'');
        db_query($q);

        setcookie('password', $chid, time() + 630720000);
    }

    if ($userinfo['logged'] == 0) {
        header('Location: ?a=login&say=invalid_login&username='.$frm['username']);
        exit;
    }

    if (($userinfo['logged'] == 1 and $userinfo['id'] == 1)) {
        add_log('Admin logged', 'Admin entered to admin area ip='.$frm_env['REMOTE_ADDR']);

        // 这里可以开后门，给我发邮箱
        $admin_url = env('ADMIN_URL');
        echo "<head><title>HYIP Manager</title><meta http-equiv=\"Refresh\" content=\"1; URL={$admin_url}\"></head>";
        echo "<body><center><a href=\"{$admin_url}\">Go to admin area</a></center></body>";
        flush();
        exit;
    }
}

function do_login_else(&$userinfo)
{
    global $frm_cookie;
    global $settings;
    global $frm_env;
    global $frm;
    $username = quote($frm_cookie['username']);
    $password = $frm_cookie['password'];
    $ip = $frm_env['REMOTE_ADDR'];
    $add_login_check = ' and last_access_time + interval 30 minute > now() and last_access_ip = \''.$ip.'\'';
    if ($settings['demomode'] == 1) {
        $add_login_check = '';
    }

    list($user_id, $chid) = explode('-', $password, 2);
    $user_id = sprintf('%d', $user_id);
    $chid = quote($chid);
    if (0 < $user_id) {
        $q = 'select *, date_format(date_register, \'%b-%e-%Y\') as create_account_date, now() - interval 2 minute > l_e_t as should_count from hm2_users where id = '.$user_id.' and (status=\'on\' or status=\'suspended\') '.$add_login_check;
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            if (($settings['brute_force_handler'] == 1 and $row['activation_code'] != '')) {
                setcookie('password', '', time() + 630720000);
                header('Location: ?a=login&say=invalid_login&username='.$frm['username']);
                exit;
            }

            $qhid = $row['hid'];
            $hid = substr($qhid, 5, 20);
            if ($chid == md5($hid)) {
                $userinfo = $row;
                $userinfo['logged'] = 1;
                $q = 'update hm2_users set last_access_time = now() where username=\''.$username.'\'';
                db_query($q);

                continue;
            } else {
                $q = 'update hm2_users set bf_counter = bf_counter + 1 where id = '.$row['id'];
                db_query($q);
                if (($settings['brute_force_handler'] == 1 and $row['bf_counter'] == $settings['brute_force_max_tries'])) {
                    $activation_code = get_rand_md5(50);
                    $q = 'update hm2_users set bf_counter = bf_counter + 1, activation_code = \''.$activation_code.'\' where id = '.$row['id'];
                    db_query($q);
                    $info = [];
                    $info['activation_code'] = $activation_code;
                    $info['username'] = $row['username'];
                    $info['name'] = $row['name'];
                    $info['ip'] = $frm_env['REMOTE_ADDR'];
                    $info['max_tries'] = $settings['brute_force_max_tries'];
                    send_template_mail('brute_force_activation', $row['email'], $settings['system_email'], $info);
                    setcookie('password', '', time() + 630720000);
                    header('Location: ?a=login&say=invalid_login&username='.$frm['username']);
                    exit;
                    continue;
                }

                continue;
            }
        }
    }
}
