<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use App\Models\Deposit;
use App\Models\History;
use Carbon\Carbon;

function is_SSL()
{
    if (! isset($_SERVER['HTTPS'])) {
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
    $sth = db_query("select time from sendmails where `to` = '$to' and `status` = 1 order by `time` desc limit 1");
    $row = mysql_fetch_assoc($sth);
    $status = 0;
    if (! isset($row['time']) || (time() - $row['time'] > 60)) {
        call_user_func_array('mail', func_get_args());
        $status = 1;
    }
    $subject = mysql_real_escape_string($subject);
    $message = mysql_real_escape_string($message);
    db_query("insert sendmails (`to`, `subject`, `message`, `time`, `status`) values ('$to', '$subject', '$message', '$time', '$status')");
}

function add_log($subject, $message)
{
    $time = time();
    $subject = mysql_real_escape_string($subject);
    db_query("insert logs (`subject`, `message`, `time`) values('$subject', '$message', '$time')");
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
        mysql_query("insert into queries (`query`, `time`) values ('$insert', '$time')");
    }
    $result = mysql_query($q);
    if ($error = mysql_error()) {
        throw new Exception($error.','.$q);
    }

    return $result;
}

function add_deposit($ec, $user_id, $amount, $batch, $account, $h_id, $compound, $ago = 0)
{
    $compound = intval($compound);
    $h_id = intval($h_id);
    $user_id = intval($user_id);
    $amount = sprintf('%.02f', $amount);

    $datetime = Carbon::now()->subDays($ago);

    // 查找投资是否已经入库
    $q = 'select count(*) as cnt from history where ec = '.$ec.' && type = \'add_funds\' && payment_batch_num = \''.$batch.'\'';
    $sth = db_query($q);
    $row = mysql_fetch_array($sth);
    if (0 < $row['cnt']) {
        return 0;
    }

    // 写history(add_funds)
    $desc = 'Add funds to account from '.app('data')->exchange_systems[$ec]['name'].('. Batch id = '.$batch);
    History::create([
        'user_id' => $user_id,
        'amount' => $amount,
        'type' => 'add_funds',
        'description' => $desc,
        'actual_amount' => $amount,
        'payment_batch_num' => $batch,
        'ec' => $ec,
        'date' => $datetime,
    ]);

    // 计算delay、compound
    $q = 'select * from types where id = '.$h_id;
    $sth = db_query($q);
    $name = '';
    $type = mysql_fetch_array($sth);
    $delay = -1;
    if ($type) {
        $delay += $type['delay'];
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
    }
    if ($delay < 0) {
        $delay = 0;
    }

    $q = 'select min(plans.min_deposit) as min, max(if(plans.max_deposit = 0, 999999999999, plans.max_deposit)) as max from types left outer join plans on types.id = plans.parent where types.id = '.$h_id;
    $sth1 = db_query($q);
    $row1 = mysql_fetch_array($sth1);
    $min_deposit = $row1['min'];
    $max_deposit = $row1['max'];
    // 判断金额是否在计划范围内
    if (($min_deposit <= $amount and $amount <= $max_deposit)) {
        // Add deposit
        $deposit = Deposit::create([
            'user_id' => $user_id,
            'type_id' => $h_id,
            'deposit_date' => $datetime,
            'last_pay_date' => $datetime->addDays($delay),
            'status' => 'on',
            'q_pays' => 0,
            'amount' => $amount,
            'actual_amount' => $amount,
            'ec' => $ec,
            'compound' => $compound,
        ]);
        // Add deposit to history
        $deposit_id = $deposit->id;
        History::create([
            'user_id' => $user_id,
            'amount' => - $amount,
            'type' => 'deposit',
            'description' => 'Deposit to '.quote($name),
            'actual_amount' => - $amount,
            'ec' => $ec,
            'date' => $datetime,
            'deposit_id' => $deposit_id,
        ]);

        // imps
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

        $ref_sum = referral_commission($user_id, $amount, $ec);
    } else {
        $name = 'Deposit to Account';
    }

    // 发邮件通知用户
    $q = 'select * from users where id = '.$user_id;
    $sth = db_query($q);
    $user = mysql_fetch_array($sth);
    $info['username'] = $user['username'];
    $info['name'] = $user['name'];
    $info['amount'] = number_format($amount, 2);
    $info['account'] = $account;
    $info['currency'] = app('data')->exchange_systems[$ec]['name'];
    $info['batch'] = $batch;
    $info['compound'] = $compound;
    $info['plan'] = $name;
    $info['ref_sum'] = $ref_sum ?? 0;
    $q = 'select email from users where id = 1';
    $sth = db_query($q);
    $admin_email = '';
    while ($row = mysql_fetch_array($sth)) {
        $admin_email = $row['email'];
    }

    if ($user['is_test'] != 1) {
        send_template_mail('deposit_admin_notification', $admin_email, app('data')->settings['system_email'], $info);
        send_template_mail('deposit_user_notification', $user['email'], app('data')->settings['system_email'], $info);
    }

    return 1;
}

function referral_commission($user_id, $amount, $ec)
{
    $ref_sum = 0;
    if (app('data')->settings['use_referal_program'] == 1) {
        $q = 'select * from users where id = '.$user_id;
        $rsth = db_query($q);
        $uinfo = mysql_fetch_array($rsth);
        $ref = 0;
        if (0 < $uinfo['ref']) {
            $ref = $uinfo['ref'];
        } else {
            return 0;
        }

        if (app('data')->settings['pay_active_referal']) {
            $q = 'select count(*) as cnt from deposits where user_id = '.$ref;
            $sth = db_query($q);
            $row = mysql_fetch_array($sth);
            if ($row['cnt'] <= 0) {
                return 0;
            }
        }

        if (app('data')->settings['use_solid_referral_commission'] == 1) {
            if (0 < app('data')->settings['solid_referral_commission_amount']) {
                $q = 'select count(*) as cnt from deposits where user_id = '.$user_id;
                $sth = db_query($q);
                $row = mysql_fetch_array($sth);
                if ($row['cnt'] == 1) {
                    $sum = app('data')->settings['solid_referral_commission_amount'];
                    $ref_sum += $sum;
                    $q = 'insert into history set
                        user_id = '.$ref.',
                        amount = '.$sum.',
                        actual_amount = '.$sum.',
                        type = \'commissions\',
                        description = \'Referral commission from '.$uinfo['username'].('\',
                        ec = '.$ec.',
                        date = now()');
                    db_query($q);
                    $q = 'select * from users where id = '.$ref;
                    $rsth = db_query($q);
                    $refinfo = mysql_fetch_array($rsth);
                    $refinfo['amount'] = number_format($sum, 2);
                    $refinfo['ref_username'] = $uinfo['username'];
                    $refinfo['ref_name'] = $uinfo['name'];
                    $refinfo['currency'] = app('data')->exchange_systems[$ec]['name'];
                    send_template_mail('referral_commision_notification', $refinfo['email'], app('data')->settings['system_email'],
                        $refinfo);
                }
            }
        } else {
            if (app('data')->settings['use_active_referal'] == 1) {
                $q = 'select count(distinct user_id) as col from users, deposits where ref = '.$ref.' and deposits.user_id = users.id';
            } else {
                $q = 'select count(*) as col from users where ref = '.$ref;
            }

            $sth = db_query($q);
            if ($row = mysql_fetch_array($sth)) {
                $col = $row['col'];
                $q = 'select percent from referal where from_value <= '.$col.' and (to_value >= '.$col.' or to_value = 0) order by from_value desc limit 1';
                $sth = db_query($q);
                if ($row = mysql_fetch_array($sth)) {
                    $sum = $amount * $row['percent'] / 100;
                    $ref_sum += $sum;
                    $q = 'insert into history set
            user_id = '.$ref.',
            amount = '.$sum.',
            actual_amount = '.$sum.',
            type = \'commissions\',
            description = \'Referral commission from '.$uinfo['username'].('\',
            ec = '.$ec.',
            date = now()');
                    db_query($q);
                    $q = 'select * from users where id = '.$ref;
                    $rsth = db_query($q);
                    $refinfo = mysql_fetch_array($rsth);
                    $refinfo['amount'] = number_format($sum, 2);
                    $refinfo['ref_username'] = $uinfo['username'];
                    $refinfo['ref_name'] = $uinfo['name'];
                    $refinfo['currency'] = app('data')->exchange_systems[$ec]['name'];
                    send_template_mail('referral_commision_notification', $refinfo['email'], app('data')->settings['system_email'],
                        $refinfo);
                }
            }
        }

        if (app('data')->settings['use_solid_referral_commission'] != 1) {
            for ($i = 2; $i < 11; ++$i) {
                if (($ref == 0 or app('data')->settings['ref'.$i.'_cms'] == 0)) {
                    break;
                }

                $q = 'select * from users where id = '.$ref;
                $sth = db_query($q);
                $ref = 0;
                while ($row = mysql_fetch_array($sth)) {
                    $ref = $row['ref'];
                    if (0 < $ref) {
                        $sum = $amount * app('data')->settings['ref'.$i.'_cms'] / 100;
                        $ref_sum += $sum;
                        $q = 'insert into history set
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
    $q = 'select * from emails where id = \''.$email_id.'\'';
    $sth = db_query($q);
    $row = mysql_fetch_array($sth);
    if (! $row) {
        return;
    }

    if (! $row['status']) {
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

    $text = preg_replace('/#site_name#/', app('data')->settings['site_name'], $text);
    $subject = preg_replace('/#site_name#/', app('data')->settings['site_name'], $subject);
    $text = preg_replace('/#site_url#/', app('data')->settings['site_url'], $text);
    $subject = preg_replace('/#site_url#/', app('data')->settings['site_url'], $subject);
    if (app('data')->settings['site_name'] == 'free') {
        $fh = fopen('mails.txt', 'a');
        fwrite($fh, 'TO: '.$to.'From: '.$from.'Subject: '.$subject.$text);
        fclose($fh);
    } else {
        send_mail($to, $subject, $text, 'From: '.$from.'Reply-To: '.$from);
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
    if (app('data')->settings['use_auto_payment'] == 1) {
        $q = 'select * from deposits where id = '.$deposit_id;
        $sth = db_query($q);
        $dep = mysql_fetch_array($sth);
        $q = 'select * from users where id = '.$dep['user_id'];
        $sth = db_query($q);
        $user = mysql_fetch_array($sth);
        if ($user['auto_withdraw'] != 1) {
            return;
        }

        $q = 'select * from types where id = '.$dep['type_id'];
        $sth = db_query($q);
        $type = mysql_fetch_array($sth);
        $amount = abs($amount);
        $success_txt = 'Return principal from deposit $'.$amount.'. Auto-withdrawal to '.$user['username'].' from '.app('data')->settings['site_name'];
        $error_txt = 'Auto-withdrawal error, tried to return '.$amount.' to e-gold account # '.$user['egold_account'].'. Error:';
        list($res, $text, $batch) = send_money_to_egold('', $amount, $user['egold_account'], $success_txt, $error_txt);
        if ($res == 1) {
            $q = 'insert into history set
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

}

function count_earning($u_id)
{
    $types = [];
    if ((app('data')->settings['use_crontab'] == 1 and $u_id != -2)) {
        return;
    }

    $q = 'select plans.* from plans, types where types.status = \'on\' and types.id = plans.parent order by parent, min_deposit';
    $sth = db_query($q);
    while ($row = mysql_fetch_array($sth)) {
        $types[$row['parent']][$row['id']] = $row;
    }

    $lines = 1;
    $u_cond = 'u.last_access_time + interval 30 minute < now() ';
    if ($u_id == -1) {
        $u_cond = '1 = 1';
        $q = 'select * from users where l_e_t + interval 15 minute < now() and status = \'on\'';
    } else {
        $q = 'select * from users where id = '.$u_id.' and status = \'on\'';
    }

    if ($u_id == -2) {
        $q = 'select * from users where status = \'on\'';
        $q = 'select distinct user_id as id from deposits where to_days(last_pay_date) < to_days(now()) order by user_id';
    }

    $sth_users = db_query($q);
    while ($row_user = mysql_fetch_array($sth_users)) {
        $row_user_id = $row_user['id'];
        $q = 'update users set l_e_t = now() where id = '.$row_user_id;
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
              deposits as d,
              types as t,
              users as u
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
                if (! isset($types[$row['type_id']])) {
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

                $q = 'select count(*) as col from history where
                to_days(date) = to_days(\''.$row['last_pay_date'].('\' + interval '.$interval.') and
                deposit_id = ').$row['id'];
                ($sth3 = db_query($q));
                $flag_exists_earning = 0;
                while ($row3 = mysql_fetch_array($sth3)) {
                    $flag_exists_earning = $row3['col'];
                }

                if ($flag_exists_earning == 0) {
                    if ((5 <= $dw and $row['work_week'] == 1)) {
                        $q = 'insert into history set user_id = '.$row['user_id'].',
                    amount = 0,
                    type = \'earning\',
                    description = \'No interest on '.($dw == 5 ? 'Saturday' : 'Sunday').'\',
                    actual_amount = 0,
                    date = \''.$row['last_pay_date'].('\' + interval '.$interval.',
                    ec = ').$row['ec'].',
                    str = \'gg\',
                    deposit_id = '.$row['id'];
                    } else {
                        $q = 'insert into history set user_id = '.$row['user_id'].(',
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
                        $q = 'insert into history set user_id = '.$row['user_id'].',
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
                                    if (! in_array($row['compound'], $cps)) {
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
                                $q = 'insert into history set user_id = '.$row['user_id'].(',
                                    amount = -'.$comp_amount.',
                                    type=\'deposit\',
                                    description = \'Compounding deposit\',
                                    actual_amount = -'.$comp_amount.',
                                    ec = ').$row['ec'].',
                                    date = \''.$row['last_pay_date'].('\' + interval '.$interval.',
                                        deposit_id = ').$row['id'];
                                        db_query($q);
                                        $q = 'update deposits set amount = amount + '.$comp_amount.',
                                    actual_amount = actual_amount + '.$comp_amount.'
                                    where id = '.$row['id'];
                                db_query($q);
                            }
                        }

                        pay_direct_earning($row['id'], $inc,
                            'date = \''.$row['last_pay_date'].('\' + interval '.$interval));
                    }
                }

                $q = 'update deposits set
                    q_pays = q_pays + 1,
                    last_pay_date = last_pay_date + interval '.$interval.' '.$status.' where id ='.$row['id'];
                db_query($q);
            }
        }

        $q = 'select * from types where q_days > 0';
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            $q_days = $row['q_days'];
            $id = $row['id'];
            if ($row['return_profit'] == 1) {
                $q = 'select * from deposits where
                type_id = '.$id.' and
                status = \'on\' and
                user_id = '.$row_user_id.' and
                (deposit_date + interval '.$q_days.' day < last_pay_date or deposit_date + interval '.$q_days.' day < now()) and
                (('.$row['withdraw_principal'].' = 0) || ('.$row['withdraw_principal'].' && (deposit_date + interval '.$row['withdraw_principal_duration'].' day < now())))
             ';
                $sth1 = db_query($q);
                while ($row1 = mysql_fetch_array($sth1)) {
                    $q = 'insert into history set
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

            $q = 'update deposits set status=\'off\' where
                    user_id = '.$row_user_id.' and
                    (deposit_date + interval '.$q_days.' day < last_pay_date or deposit_date + interval '.$q_days.' day < now()) and
                    (('.$row['withdraw_principal'].' = 0) || ('.$row['withdraw_principal'].' && (deposit_date + interval '.$row['withdraw_principal_duration'].' day < now()))) and type_id = '.$id;
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
    $q = 'select type, sum(actual_amount) as sum from history where user_id = '.$id.' group by type';
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
    $q = 'select sum(actual_amount) as sum from deposits where user_id = '.$id.' and status=\'on\'';
    $sth = db_query($q);
    while ($row = mysql_fetch_array($sth)) {
        $accounting['active_deposit'] += $row['sum'];
    }

    return $accounting;
}
