<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$amount = sprintf('%0.2f', app('data')->frm['amount']);
$h_id = sprintf('%d', app('data')->frm['h_id']);
if ((app('data')->settings['use_add_funds'] and $h_id == -1)) {
    if (0.01 <= $amount) {
        $orderNo = add_deposit_order($amount, 2, [
            'plan_id' => $h_id,
            'compound' => sprintf('%d', sprintf('%d', app('data')->frm['compound'])),
        ]);
        view_assign('amount', $amount);
        view_assign('amount_format', number_format(app('data')->frm['amount'], 2, '.', ''));
        view_execute('deposit_payeer_confirm.blade.php');
    }
} else {
    $q = 'select * from types where id = '.$h_id.' and closed = 0';
    $sth = db_query($q);
    $type = mysql_fetch_array($sth);
    if (! $type) {
        view_assign('false_data', 1);
    } else {
        $plan_name = $type['name'];
        view_assign('plan_name', $plan_name);
    }

    $use_compound = 0;
    if ($type['use_compound']) {
        if ($type['compound_max_deposit'] == 0) {
            $type['compound_max_deposit'] = $amount + 1;
        }

        if (($type['compound_min_deposit'] <= $amount and $amount <= $type['compound_max_deposit'])) {
            $use_compound = 1;
            if ($type['compound_percents_type'] == 1) {
                $cps = preg_split('/\\s*,\\s*/', $type['compound_percents']);
                $cps1 = [];
                foreach ($cps as $cp) {
                    array_push($cps1, sprintf('%d', $cp));
                }

                sort($cps1);
                $compound_percents = [];
                foreach ($cps1 as $cp) {
                    array_push($compound_percents, ['percent' => sprintf('%d', $cp)]);
                }

                view_assign('compound_percents', $compound_percents);
            } else {
                view_assign('compound_min_percents', $type['compound_min_percent']);
                view_assign('compound_max_percents', $type['compound_max_percent']);
            }
        }
    }
    view_assign('use_compound', $use_compound);

    $q = 'select count(*) as col, min(min_deposit) as min from plans where parent = '.$h_id;
    $sth = db_query($q);
    $row = mysql_fetch_array($sth);
    if ($row) {
        if ($row['col'] == 0) {
            view_assign('false_data', 1);
        }

        if ($amount < $row['min']) {
            $amount = $row['min'];
        }
    } else {
        view_assign('false_data', 1);
    }

    $q = 'select count(*) as col from plans where parent = '.$h_id.' and max_deposit = 0';
    $sth = db_query($q);
    $row = mysql_fetch_array($sth);
    if ($row) {
        if (0 < $row['col']) {
        } else {
            $q = 'select count(*) as col, max(max_deposit) as max from plans where parent = '.$h_id;
            $sth = db_query($q);
            $row = mysql_fetch_array($sth);
            if ($row) {
                if ($row['col'] == 0) {
                    view_assign('false_data', 1);
                }

                if ($row['max'] < $amount) {
                    $amount = $row['max'];
                }
            } else {
                view_assign('false_data', 1);
            }
        }

        $site_name = app('data')->settings['site_name'];
        $m_desc = "Deposit to {$site_name} User {$userinfo['username']}";
        $m_desc = base64_encode($m_desc);
        $orderNo = add_deposit_order($amount, 2, [
            'plan_id' => $h_id,
            'compound' => sprintf('%d', sprintf('%d', app('data')->frm['compound'])),
        ]);

        $arHash = [
            psconfig('pe.shop_id'), // m_shop
            $orderNo, // m_orderid
            $amount, // m_amount
            'USD', // m_curr
            $m_desc, // m_desc
            psconfig('pe.shop_secret_key'), //m_key
        ];
        $m_sign = strtoupper(hash('sha256', implode(':', $arHash)));

        view_assign('m_desc', $m_desc);
        view_assign('m_sign', $m_sign);

        view_assign('m_orderid', $orderNo);
        view_assign('amount', $amount);
        view_assign('amount_format', number_format($amount, 2, '.', ''));
        view_execute('deposit_payeer_confirm.blade.php');
    }
}
