<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use App\Models\PayError;
use App\Exceptions\RedirectException;
use App\Models\History;
use Carbon\Carbon;

if (app('data')->frm['action'] == 'preview') {
    $ab = get_user_balance($userinfo['id']);
    $amount = sprintf('%0.2f', app('data')->frm['amount']);
    $description = quote(app('data')->frm['comment']);
    $ec = sprintf('%d', app('data')->frm['ec']);
    if (0 < app('data')->settings['forbid_withdraw_before_deposit']) {
        $q = 'select count(*) as cnt from deposits where user_id = '.$userinfo['id'];
        $sth = db_query($q);
        $row = mysql_fetch_array($sth);
        if ($row['cnt'] < 1) {
            throw new RedirectException('/?a=withdraw&say=no_deposits');
        }
    }

    if ($amount <= 0) {
        throw new RedirectException('/?a=withdraw&say=zero');
    }

    $on_hold = 0;
    if (app('data')->settings['allow_withdraw_when_deposit_ends'] == 1) {
        $q = 'select id from deposits where user_id = '.$userinfo['id'];
        $sth = db_query($q);
        $deps = [];
        $deps[0] = 0;
        while ($row = mysql_fetch_array($sth)) {
            array_push($deps, $row[id]);
        }

        $q = 'select sum(actual_amount) as amount from history where user_id = '.$userinfo['id'].(' and ec = '.$ec.' and
	deposit_id in (').join(',', $deps).') and
			(type=\'earning\' or
	(type=\'deposit\' and (description like \'Compou%\' or description like \'<b>Archived transactions</b>:<br>Compound%\')));';
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            $on_hold = $row[amount];
        }
    }

    if (app('data')->settings['hold_only_first_days'] == 1) {
        $q = 'select deposits.id, types.hold from deposits, types where user_id = '.$userinfo[id].' and types.id = deposits.type_id and ec='.$ec.' and deposits.deposit_date + interval types.hold day > now()';
    } else {
        $q = 'select deposits.id, types.hold from deposits, types where user_id = '.$userinfo[id].' and types.id = deposits.type_id and ec='.$ec;
    }

    $sth = db_query($q);
    $deps = [];
    $deps[0] = 0;
    while ($row = mysql_fetch_array($sth)) {
        $q = 'select sum(actual_amount) as amount from history where user_id = '.$userinfo['id'].(' and ec = '.$ec.' and
		deposit_id = '.$row[id].' and date > now() - interval '.$row[hold].' day and
			(type=\'earning\' or
		(type=\'deposit\' and (description like \'Compou%\' or description like \'<b>Archived transactions</b>:<br>Compound%\')));');
        ($sth1 = db_query($q));
        while ($row1 = mysql_fetch_array($sth1)) {
            $on_hold += $row1[amount];
        }
    }

    $q = 'select sum(actual_amount) as amount from history where user_id = '.$userinfo['id'].(' and ec = '.$ec);
    $sth = db_query($q);
    $ab['total'] = 0;
    while ($row = mysql_fetch_array($sth)) {
        $ab['total'] = $row['amount'] - $on_hold;
    }

    if ($ab['total'] < $amount) {
        if ($amount <= $ab['total'] + $on_hold) {
            throw new RedirectException('/?a=withdraw&say=on_hold');
        }
        throw new RedirectException('/?a=withdraw&say=not_enought');
    }

    if ($amount < app('data')->settings['min_withdrawal_amount']) {
        throw new RedirectException('/?a=withdraw&say=less_min');
    }

    if (0 < app('data')->settings[max_daily_withdraw]) {
        $q = 'select sum(actual_amount) as am from history where type in (\'withdraw\', \'withdraw_pending\') and user_id = '.$userinfo[id];
        $sth = db_query($q);
        $dw = 0;
        while ($row = mysql_fetch_array($sth)) {
            $dw = 0 - $row[am];
        }

        if (app('data')->settings[max_daily_withdraw] < $dw + $amount) {
            throw new RedirectException('/?a=withdraw&say=daily_limit');
        }
    }

    $fee = floor($amount * app('data')->settings['withdrawal_fee']) / 100;
    if ($fee < app('data')->settings['withdrawal_fee_min']) {
        $fee = app('data')->settings['withdrawal_fee_min'];
    }

    $to_withdraw = $amount - $fee;
    if ($to_withdraw < 0) {
        $to_withdraw = 0;
    }

    $to_withdraw = number_format(floor($to_withdraw * 100) / 100, 2);
    $account = '';

    if ($ec == 1 and psconfig('pm.marchant_id')) {
        $account = $userinfo['perfectmoney_account'];
    }

    if ($ec == 2 and psconfig('pe.shop_id')) {
        $account = $userinfo['payeer_account'];
    }
    if ($ec == 3 and psconfig('as.user_name')) {
        $account = $userinfo['bitcoin_account'];
    }

    view_assign('preview', 1);
    view_assign('amount', $amount);
    view_assign('fee', $fee);
    view_assign('to_withdraw', $to_withdraw);
    view_assign('currency', app('data')->exchange_systems[$ec]['name']);
    view_assign('ec', $ec);
    view_assign('account', $account);
    view_assign('comment', app('data')->frm['comment']);
    view_execute('withdrawal.blade.php');
} else {
    if (app('data')->frm['action'] == 'withdraw') {
        if ((app('data')->settings['use_transaction_code'] == 1 and app('data')->frm['transaction_code'] != $userinfo['transaction_code'])) {
            throw new RedirectException('/?a=withdraw&say=invalid_transaction_code');
        }

        $ab = get_user_balance($userinfo['id']);
        $amount = sprintf('%0.2f', app('data')->frm['amount']);
        $description = quote(app('data')->frm['comment']);
        $ec = sprintf('%d', app('data')->frm['ec']);
        if ($amount <= 0) {
            throw new RedirectException('/?a=withdraw&say=zero');
        }

        if (0 < app('data')->settings['forbid_withdraw_before_deposit']) {
            $q = 'select count(*) as cnt from deposits where user_id = '.$userinfo['id'];
            $sth = db_query($q);
            $row = mysql_fetch_array($sth);
            if ($row['cnt'] < 1) {
                throw new RedirectException('/?a=withdraw&say=no_deposits');
            }
        }

        $on_hold = 0;
        if (app('data')->settings['allow_withdraw_when_deposit_ends'] == 1) {
            $q = 'select id from deposits where user_id = '.$userinfo['id'];
            $sth = db_query($q);
            $deps = [];
            $deps[0] = 0;
            while ($row = mysql_fetch_array($sth)) {
                array_push($deps, $row[id]);
            }

            $q = 'select sum(actual_amount) as amount from history where user_id = '.$userinfo['id'].(' and ec = '.$ec.' and
	deposit_id in (').join(',', $deps).') and
			(type=\'earning\' or
	(type=\'deposit\' and (description like \'Compou%\' or description like \'<b>Archived transactions</b>:<br>Compound%\')));';
            $sth = db_query($q);
            while ($row = mysql_fetch_array($sth)) {
                $on_hold = $row[amount];
            }
        }

        if (app('data')->settings['hold_only_first_days'] == 1) {
            $q = 'select deposits.id, types.hold from deposits, types where user_id = '.$userinfo[id].' and types.id = deposits.type_id and ec='.$ec.' and deposits.deposit_date + interval types.hold day > now()';
        } else {
            $q = 'select deposits.id, types.hold from deposits, types where user_id = '.$userinfo[id].' and types.id = deposits.type_id and ec='.$ec;
        }

        $sth = db_query($q);
        $deps = [];
        $deps[0] = 0;
        while ($row = mysql_fetch_array($sth)) {
            $q = 'select sum(actual_amount) as amount from history where user_id = '.$userinfo['id'].(' and ec = '.$ec.' and
		deposit_id = '.$row[id].' and date > now() - interval '.$row[hold].' day and
			(type=\'earning\' or
		(type=\'deposit\' and (description like \'Compou%\' or description like \'<b>Archived transactions</b>:<br>Compound%\')));');
            ($sth1 = db_query($q));
            while ($row1 = mysql_fetch_array($sth1)) {
                $on_hold += $row1[amount];
            }
        }

        $q = 'select sum(actual_amount) as amount from history where user_id = '.$userinfo['id'].(' and ec = '.$ec);
        $sth = db_query($q);
        $ab['total'] = 0;
        while ($row = mysql_fetch_array($sth)) {
            $ab['total'] = $row['amount'] - $on_hold;
        }

        if ($ab['total'] < $amount) {
            if ($amount <= $ab['total'] + $on_hold) {
                throw new RedirectException('/?a=withdraw&say=on_hold');
            }
            throw new RedirectException('/?a=withdraw&say=not_enought');
        }

        if (0 < app('data')->settings[max_daily_withdraw]) {
            $q = 'select sum(actual_amount) as am from history where type in (\'withdraw\', \'withdraw_pending\') and user_id = '.$userinfo[id];
            $sth = db_query($q);
            $dw = 0;
            while ($row = mysql_fetch_array($sth)) {
                $dw = 0 - $row[am];
            }

            if (app('data')->settings[max_daily_withdraw] < $dw + $amount) {
                throw new RedirectException('/?a=withdraw&say=daily_limit');
            }
        }

        if ($amount <= $ab['total']) {
            if ($amount < app('data')->settings['min_withdrawal_amount']) {
                throw new RedirectException('/?a=withdraw&say=less_min');
            }

            $history = History::create([
                'user_id' => $userinfo['id'],
                'amount' => - $amount,
                'type' => 'withdraw_pending',
                'description' => $description,
                'actual_amount' => - $amount,
                'ec' => $ec,
                'date' => Carbon::now(),
            ]);
            $last_id = $history->id;
            $info = [];
            $info['username'] = $userinfo['username'];
            $info['name'] = $userinfo['name'];
            $info['ip'] = app('data')->env['REMOTE_ADDR'];
            $info['amount'] = $amount;
            if (app('data')->settings['use_auto_payment'] == 1 &&
                app('data')->settings['min_auto_withdraw'] <= $amount &&
                app('data')->settings['max_auto_withdraw'] >= $amount) {
                $q = 'select sum(amount) as sum from history where type=\'withdrawal\' and date + interval 24 hour > now() and user_id = '.$userinfo['id'];
                $sth = db_query($q);
                if ($row = mysql_fetch_array($sth)) {
                    if ((abs($row['sum']) + $amount <= app('data')->settings['max_auto_withdraw_user'] and $userinfo['auto_withdraw'] == 1)) {
                        $fee = floor($amount * app('data')->settings['withdrawal_fee']) / 100;
                        if ($fee < app('data')->settings['withdrawal_fee_min']) {
                            $fee = app('data')->settings['withdrawal_fee_min'];
                        }

                        $to_withdraw = $amount - $fee;
                        if ($to_withdraw < 0) {
                            $to_withdraw = 0;
                        }

                        $to_withdraw = sprintf('%.02f', floor($to_withdraw * 100) / 100);
                        $memo = 'Withdraw to '.$userinfo['username'].' from '.app('data')->settings['site_name'];

                        $payment_account = '';
                        try {
                            if ($ec == 1) {
                                $payment_account = $userinfo['perfectmoney_account'];
                                $batch = send_money_to_perfectmoney($to_withdraw, $payment_account, $memo);
                            }
                            if ($ec == 2) {
                                $payment_account = $userinfo['payeer_account'];
                                $batch = send_money_to_payeer($to_withdraw, $payment_account, $memo);
                            }
                            if ($ec == 3) {
                                $payment_account = $userinfo['bitcoin_account'];
                                $batch = send_money_to_bitcoin($to_withdraw, $payment_account, $memo);
                            }
                        } catch (Exception $e) {
                            $username = $userinfo['username'];
                            PayError::create([
                                'data' => compact('to_withdraw', 'username'),
                                'error' => $e->getMessage(),
                            ]);
                        }
                        $q = 'delete from history where id = '.$last_id;
                        db_query($q);
                        $history = History::create([
                            'user_id' => $userinfo['id'],
                            'amount' => - $amount,
                            'type' => 'withdrawal',
                            'description' => "Withdraw to account {$payment_account}. Batch is {$batch}",
                            'actual_amount' => - $amount,
                            'payment_batch_num' => $batch,
                            'ec' => $ec,
                            'date' => Carbon::now(),
                        ]);
                        $info['batch'] = $batch;
                        $info['account'] = $payment_account;
                        $info['currency'] = app('data')->exchange_systems[$ec]['name'];
                        send_template_mail('withdraw_user_notification', $userinfo['email'], $info);
                        send_template_mail('withdraw_admin_notification', app('data')->settings['system_email'], $info);
                        throw new RedirectException('/?a=withdraw&say=processed&batch='.$batch);
                    }
                }
            } else {
                send_template_mail('withdraw_request_user_notification', $userinfo['email'], $info);
                send_template_mail('withdraw_request_admin_notification', app('data')->settings['system_email'], $info);
            }

            throw new RedirectException('/?a=withdraw&say=processed');
        }
        if ($amount <= $ab[total] + $on_hold) {
            throw new RedirectException('/?a=withdraw&say=on_hold');
        }
        throw new RedirectException('/?a=withdraw&say=not_enought');
    }
    $id = $userinfo['id'];
    $ab = get_user_balance($id);
    $ab_formated = [];
    $ab['withdraw_pending'] = 0 - $ab['withdraw_pending'];
    reset($ab);
    while (list($kk, $vv) = each($ab)) {
        $vv = floor($vv * 100) / 100;
        $ab_formated[$kk] = number_format($vv, 2);
    }

    view_assign('ab_formated', $ab_formated);
    view_assign('say', app('data')->frm['say']);
    view_assign('batch', app('data')->frm['batch']);
    $format = (app('data')->settings['show_full_sum'] ? 5 : 2);
    $q = 'select sum(actual_amount) as sm, ec from history where user_id = '.$userinfo['id'].' group by ec';
    $sth = db_query($q);
    while ($row = mysql_fetch_array($sth)) {
        if ($format == 2) {
            $row['sm'] = floor($row['sm'] * 100) / 100;
        }

        app('data')->exchange_systems[$row['ec']]['balance'] = number_format($row['sm'], $format);
        if (100 < $row['ec']) {
            view_assign('other_processings', 1);
            continue;
        }
    }

    $ps = [];
    reset(app('data')->exchange_systems);
    foreach (app('data')->exchange_systems as $id => $data) {
        array_push($ps, array_merge(['id' => $id, 'account' => $accounts[$id]], $data));
    }

    $hold = [];
    if (app('data')->settings['allow_withdraw_when_deposit_ends'] == 1) {
        $q = 'select id from deposits where user_id = '.$userinfo['id'].' and status=\'on\'';
        $sth = db_query($q);
        $deps = [];
        $deps[0] = 0;
        while ($row = mysql_fetch_array($sth)) {
            array_push($deps, $row[id]);
        }

        $q = 'select sum(actual_amount) as amount, ec from history where user_id = '.$userinfo['id'].' and
	deposit_id in ('.join(',', $deps).') and
			(type=\'earning\' or
	(type=\'deposit\' and (description like \'Compou%\' or description like \'<b>Archived transactions</b>:<br>Compound%\'))) group by ec';
        $sth = db_query($q);
        while ($row = mysql_fetch_array($sth)) {
            array_push($hold, ['ec' => $row[ec], 'amount' => number_format($row[amount], 2)]);
        }
    }

    if (app('data')->settings['hold_only_first_days'] == 1) {
        $q = 'select sum(history.actual_amount) as am, history.ec
                    from
                      history,
                      deposits,
                      types
                    where
                      history.user_id = '.$userinfo[id].' and
                    history.deposit_id = deposits.id and
                      types.id = deposits.type_id and
                      now() - interval types.hold day < history.date and
                    deposits.deposit_date + interval types.hold day > now() and
                    (history.type=\'earning\' or
                    (history.type=\'deposit\' and (history.description like \'Compou%\' or history.description like \'<b>Archived transactions</b>:<br>Compound%\')))
                    group by history.ec';
    } else {
        $q = 'select sum(history.actual_amount) as am,
                      history.ec
                        from
                          history,
                          deposits,
                          types
                        where
                          history.user_id = '.$userinfo[id].' and
                      history.deposit_id = deposits.id and
                          types.id = deposits.type_id and
                          now() - interval types.hold day < history.date and
                      (history.type=\'earning\' or
                    (history.type=\'deposit\' and (history.description like \'Compou%\' or history.description like \'<b>Archived transactions</b>:<br>Compound%\')))
                    group by history.ec';
    }

    $sth = db_query($q);
    $deps = [];
    $deps[0] = 0;
    while ($row = mysql_fetch_array($sth)) {
        array_push($hold, ['ec' => $row[ec], 'amount' => number_format($row[am], 2)]);
    }

    view_assign('hold', $hold);
    view_assign('ps', $ps);
    view_execute('withdrawal.blade.php');
}
