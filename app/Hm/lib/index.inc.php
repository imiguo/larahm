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
use App\Exceptions\RedirectException;
use Illuminate\Support\Facades\Cookie;

function bind_ref()
{
    Cookie::queue('referer', app('data')->frm['ref'], 43200);
    if (Cookie::get('referer') == '') {
        $ref = quote(app('data')->frm['ref']);
        $q = 'select id from users where username = \''.$ref.'\'';
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            $ref_id = $row['id'];
            $q = 'select * from referal_stats where date = current_date() and user_id = '.$ref_id;
            $sth = db_query($q);
            $f = 0;
            while ($row = mysql_fetch_array($sth)) {
                $f = 1;
            }

            if ($f == 0) {
                $q = 'insert into referal_stats set date = current_date(), user_id = '.$ref_id.', income = 1, reg = 0';
                db_query($q);
            } else {
                $q = 'update referal_stats set income = income+1 where date = current_date() and user_id = '.$ref_id.' ';
                db_query($q);
            }

            break;
        }
    }
}

function custom2_pay_withdraw_eeecurrency()
{
    $batch = app('data')->frm['TRANSACTION_ID'];
    list($id, $str) = explode('-', app('data')->frm['CUSTOM1']);
    $id = sprintf('%d', $id);
    if ($str == '') {
        $str = 'abcdef';
    }

    $str = quote($str);
    $q = 'select * from history where id = '.$id.' and str = \''.$str.'\'';
    $sth = db_query($q);
    while ($row = mysql_fetch_array($sth)) {
        $q = 'delete from history where id = '.$id;
        db_query($q);
        $q = 'insert into history set
            user_id = '.$row['user_id'].',
            amount = -'.abs($row['amount']).(',
            type = \'withdrawal\',
            description = \'Withdraw processed. Batch id = '.$batch.'\',
            actual_amount = -').abs($row['amount']).',
            ec = 8,
            date = now()';
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
        $info['currency'] = app('data')->exchange_systems[8]['name'];
        send_template_mail('withdraw_user_notification', $userinfo['email'], app('data')->settings['system_email'], $info);
    }
}

function custom2_pay_withdraw()
{
    $batch = app('data')->frm['TRANSACTION_ID'];
    list($id, $str) = explode('-', app('data')->frm['CUSTOM1']);
    $id = sprintf('%d', $id);
    if ($str == '') {
        $str = 'abcdef';
    }

    $str = quote($str);
    $q = 'select * from history where id = '.$id.' and str = \''.$str.'\'';
    $sth = db_query($q);
    while ($row = mysql_fetch_array($sth)) {
        $q = 'delete from history where id = '.$id;
        db_query($q);
        $q = 'insert into history set
            user_id = '.$row['user_id'].',
            amount = -'.abs($row['amount']).(',
            type = \'withdrawal\',
            description = \'Withdraw processed. Batch id = '.$batch.'\',
            actual_amount = -').abs($row['amount']).',
            ec = 2,
            date = now()';
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
}

function user3_pay_withdraw_payment()
{
    $batch = app('data')->frm['transaction_id'];
    list($id, $str) = explode('-', app('data')->frm['user1']);
    $id = sprintf('%d', $id);
    if ($str == '') {
        $str = 'abcdef';
    }

    $str = quote($str);
    $q = 'select * from history where id = '.$id.' and str = \''.$str.'\'';
    $sth = db_query($q);
    while ($row = mysql_fetch_array($sth)) {
        $q = 'delete from history where id = '.$id;
        db_query($q);
        $q = 'insert into history set
            user_id = '.$row['user_id'].',
            amount = -'.abs($row['amount']).(',
            type = \'withdrawal\',
            description = \'Withdraw processed. Batch id = '.$batch.'\',
            actual_amount = -').abs($row['amount']).',
            ec = 4,
            date = now()';
        db_query($q);
        $q = 'select * from users where id = '.$row['user_id'];
        $sth = db_query($q);
        $userinfo = mysql_fetch_array($sth);
        $info = [];
        $info['username'] = $userinfo['username'];
        $info['name'] = $userinfo['name'];
        $info['amount'] = sprintf('%.02f', abs($row['amount']));
        $info['account'] = app('data')->frm['payee_email'];
        $info['batch'] = $batch;
        $info['paying_batch'] = $batch;
        $info['receiving_batch'] = $batch;
        $info['currency'] = app('data')->exchange_systems[4]['name'];
        send_template_mail('withdraw_user_notification', $userinfo['email'], app('data')->settings['system_email'], $info);
    }
}

function show_info_box($stats)
{
    if (app('data')->settings['show_info_box_members_online'] == 1) {
        if (app('data')->settings['crontab_stats'] == 1) {
            app('data')->settings['show_info_box_members_online_generated'] = $stats['visitors'];
        } else {
            $q = 'select count(*) as col from users where last_access_time + interval 30 minute > now()';
            $sth = db_query($q);
            $row = mysql_fetch_array($sth);
            app('data')->settings['show_info_box_members_online_generated'] = $row['col'];
        }
    }

    if (app('data')->settings['show_info_box_total_accounts'] == 1) {
        if (app('data')->settings['crontab_stats'] == 1) {
            app('data')->settings['info_box_total_accounts_generated'] = $stats['total_users'];
        } else {
            $q = 'select count(*) as col from users where id > 1';
            $sth = db_query($q);
            $row = mysql_fetch_array($sth);
            app('data')->settings['info_box_total_accounts_generated'] = $row['col'];
        }
    }

    if (app('data')->settings['show_info_box_active_accounts'] == 1) {
        if (app('data')->settings['crontab_stats'] == 1) {
            app('data')->settings['info_box_total_active_accounts_generated'] = $stats['active_accounts'];
        } else {
            $q = 'select count(distinct user_id) as col from deposits ';
            $sth = db_query($q);
            $row = mysql_fetch_array($sth);
            app('data')->settings['info_box_total_active_accounts_generated'] = $row['col'];
        }
    }

    if (app('data')->settings['show_info_box_vip_accounts'] == 1) {
        $q = 'select count(distinct user_id) as col from deposits where actual_amount > '.sprintf('%.02f',
                app('data')->settings['vip_users_deposit_amount']);
        $sth = db_query($q);
        $row = mysql_fetch_array($sth);
        app('data')->settings['info_box_total_vip_accounts_generated'] = $row['col'];
    }

    if (app('data')->settings['show_info_box_deposit_funds'] == 1) {
        if (app('data')->settings['crontab_stats'] == 1) {
            app('data')->settings['info_box_deposit_funds_generated'] = number_format($stats['total_deposited'], 2);
        } else {
            $q = 'select sum(amount) as sum from deposits';
            $sth = db_query($q);
            $row = mysql_fetch_array($sth);
            app('data')->settings['info_box_deposit_funds_generated'] = number_format($row['sum'], 2);
        }
    }

    if (app('data')->settings['show_info_box_today_deposit_funds'] == 1) {
        $q = 'select sum(amount) as sum from deposits where to_days(deposit_date) = to_days(now() + interval '.app('data')->settings['time_dif'].' day)';
        $sth = db_query($q);
        $row = mysql_fetch_array($sth);
        app('data')->settings['info_box_today_deposit_funds_generated'] = number_format($row['sum'], 2);
    }

    if (app('data')->settings['show_info_box_total_withdraw'] == 1) {
        if (app('data')->settings['crontab_stats'] == 1) {
            app('data')->settings['info_box_withdraw_funds_generated'] = number_format(abs($stats['total_withdraw']), 2);
        } else {
            $q = 'select sum(amount) as sum from history where type=\'withdrawal\'';
            $sth = db_query($q);
            $row = mysql_fetch_array($sth);
            app('data')->settings['info_box_withdraw_funds_generated'] = number_format(abs($row['sum']), 2);
        }
    }

    if (app('data')->settings['show_info_box_visitor_online'] == 1) {
        $q = 'select count(*) as sum from online';
        $sth = db_query($q);
        $row = mysql_fetch_array($sth);
        app('data')->settings['info_box_visitor_online_generated'] = $row['sum'];
    }

    if (app('data')->settings['show_info_box_newest_member'] == 1) {
        $q = 'select username from users where status = \'on\' order by id desc limit 0,1';
        $sth = db_query($q);
        $row = mysql_fetch_array($sth);
        app('data')->settings['show_info_box_newest_member_generated'] = $row['username'];
    }

    if (app('data')->settings['show_info_box_last_update'] == 1) {
        app('data')->settings['show_info_box_last_update_generated'] = date('M j, Y', time() + app('data')->settings['time_dif'] * 60 * 60);
    }
}

function do_login(&$userinfo)
{
    $username = quote(app('data')->frm['username']);
    $password = quote(app('data')->frm['password']);
    $add_opt_in_check = '';
    if (app('data')->settings['use_opt_in'] == 1) {
        $add_opt_in_check = ' and (confirm_string = "" or confirm_string is NULL)';
    }

    $q = 'select *, date_format(date_register, \'%b-%e-%Y\') as create_account_date, now() - interval 2 minute > l_e_t as should_count from users where username = \''.$username.'\' and (status=\'on\' or status=\'suspended\') '.$add_opt_in_check;
    $sth = db_query($q);
    if ($row = mysql_fetch_array($sth)) {
        if ((app('data')->settings['brute_force_handler'] == 1 and $row['activation_code'] != '')) {
            throw new RedirectException('/?a=login&say=invalid_login&username='.app('data')->frm['username']);
        }
        if ((app('data')->settings['brute_force_handler'] == 1 and $row['bf_counter'] == app('data')->settings['brute_force_max_tries'])) {
            $activation_code = get_rand_md5(50);
            $q = 'update users set bf_counter = bf_counter + 1, activation_code = \''.$activation_code.'\' where id = '.$row['id'];
            db_query($q);
            $info = [];
            $info['activation_code'] = $activation_code;
            $info['username'] = $row['username'];
            $info['name'] = $row['name'];
            $info['ip'] = app('data')->env['REMOTE_ADDR'];
            $info['max_tries'] = app('data')->settings['brute_force_max_tries'];
            send_template_mail('brute_force_activation', $row['email'], app('data')->settings['system_email'], $info);
            throw new RedirectException('/?a=login&say=invalid_login&username='.app('data')->frm['username']);
        }
        if (! app('hash')->check($password, $row['password'])) {
            $q = 'update users set bf_counter = bf_counter + 1 where id = '.$row['id'];
            db_query($q);
            throw new RedirectException('/?a=login&say=invalid_login&username='.app('data')->frm['username']);
        }

        $userinfo = $row;
        $userinfo['logged'] = 1;
        $ip = app('data')->env['REMOTE_ADDR'];
        $q = 'update users set bf_counter = 0, last_access_time = now(), last_access_ip = \''.$ip.'\' where id = '.$row['id'];
        db_query($q);
        $q = 'insert into user_access_log set user_id = '.$userinfo['id'].(', date = now(), ip = \''.$ip.'\'');
        db_query($q);
    }

    if ($userinfo['logged'] == 0) {
        throw new RedirectException('/?a=login&say=invalid_login&username='.app('data')->frm['username']);
    }
}

function do_login_else(&$userinfo)
{
    if (Auth::check()) {
        $user_id = Auth::id();
        $q = 'select *, date_format(date_register, \'%b-%e-%Y\') as create_account_date, now() - interval 2 minute > l_e_t as should_count from users where id = '.$user_id.' and (status=\'on\' or status=\'suspended\')';
        $sth = db_query($q);
        if ($row = mysql_fetch_array($sth)) {
            $q = 'update users set last_access_time = now() where username=\''.$row['username'].'\'';
            $userinfo = $row;
            $userinfo['logged'] = 1;
            db_query($q);
        } else {
            Auth::logout();
        }
    }
}
