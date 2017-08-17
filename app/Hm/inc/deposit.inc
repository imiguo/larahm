<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$ab = get_user_balance($userinfo['id']);
$ab_formated = [];
while (list($kk, $vv) = each($ab)) {
    $ab_formated[$kk] = number_format($vv, 2);
}

view_assign('ab_formated', $ab_formated);
view_assign('frm', app('data')->frm);
$q = 'select type_id from hm2_deposits where user_id = '.$userinfo['id'];
$sth = db_query($q);
$already_deposits = [];
while ($row = mysql_fetch_array($sth)) {
    array_push($already_deposits, $row['type_id']);
}

$q = 'select * from hm2_types where status = \'on\' and closed = 0 order by id';
$sth = db_query($q);
$plans = [];
$i = 0;
$min_deposit = 1000000000;
while ($row = mysql_fetch_array($sth)) {
    if (0 < $row['parent']) {
        if (!in_array($row['parent'], $already_deposits)) {
            continue;
        }
    }

    ++$i;
    if (($row['use_compound'] == 1 and (($i == 1 and app('data')->frm['h_id'] == '') or app('data')->frm['h_id'] == $row['id']))) {
        view_assign('default_check_compound', 1);
    }

    $compounding_available += $row['use_compound'];
    $q = 'select * from hm2_plans where parent = '.$row['id'].' order by id';
    if (!($sth1 = db_query($q))) {
    }

    $row['plans'] = [];
    while ($row1 = mysql_fetch_array($sth1)) {
        $row1['deposit'] = '';
        $min_deposit = ($row1['min_deposit'] < $min_deposit ? $row1['min_deposit'] : $min_deposit);
        if ($row1['max_deposit'] == 0) {
            $row1['deposit'] = '$'.number_format($row1['min_deposit']).' and more';
        } else {
            $row1['deposit'] = '$'.number_format($row1['min_deposit']).' - $'.number_format($row1['max_deposit']);
        }

        array_push($row['plans'], $row1);
    }

    $periods = ['d' => 'Daily', 'w' => 'Weekly', 'b-w' => 'Bi Weekly', 'm' => 'Monthly', 'y' => 'Yearly'];
    $row['period'] = $periods[$row['period']];
    array_push($plans, $row);
}

$q = 'select sum(actual_amount) as sm, ec from hm2_history where user_id = '.$userinfo['id'].' group by ec';
$sth = db_query($q);
while ($row = mysql_fetch_array($sth)) {
    app('data')->exchange_systems[$row['ec']]['balance'] = number_format($row['sm'], 2);
}

$ps = [];
reset(app('data')->exchange_systems);
foreach (app('data')->exchange_systems as $id => $data) {
    array_push($ps, array_merge(['id' => $id], $data));
}
view_assign('ps', $ps);
$hold = [];
if (app('data')->settings['allow_withdraw_when_deposit_ends'] == 1) {
    $q = 'select id from hm2_deposits where user_id = '.$userinfo['id'];
    $sth = db_query($q);
    $deps = [];
    $deps[0] = 0;
    while ($row = mysql_fetch_array($sth)) {
        array_push($deps, $row[id]);
    }

    $q = 'select sum(actual_amount) as amount, ec from hm2_history where user_id = '.$userinfo['id'].' and
	deposit_id in ('.join(',', $deps).') and
			(type=\'earning\' or
	(type=\'deposit\' and (description like \'Compou%\' or description like \'<b>Archived transactions</b>:<br>Compound%\'))) group by ec';
    $sth = db_query($q);
    while ($row = mysql_fetch_array($sth)) {
        array_push($hold, ['ec' => $row[ec], 'amount' => number_format($row[amount], 2)]);
    }
}

if (app('data')->settings['hold_only_first_days'] == 1) {
    $q = 'select
              sum(hm2_history.actual_amount) as am,
	      hm2_history.ec
            from
              hm2_history,
              hm2_deposits,
              hm2_types
            where
              hm2_history.user_id = '.$userinfo[id].' and
	      hm2_history.deposit_id = hm2_deposits.id and
              hm2_types.id = hm2_deposits.type_id and
              now() - interval hm2_types.hold day < hm2_history.date and
              hm2_deposits.deposit_date + interval hm2_types.hold day > now() and
	      (hm2_history.type=\'earning\' or
		(hm2_history.type=\'deposit\' and (hm2_history.description like \'Compou%\' or hm2_history.description like \'<b>Archived transactions</b>:<br>Compound%\')))
	    group by hm2_history.ec
          ';
} else {
    $q = 'select
              sum(hm2_history.actual_amount) as am,
	      hm2_history.ec
            from
              hm2_history,
              hm2_deposits,
              hm2_types
            where
              hm2_history.user_id = '.$userinfo[id].' and
	      hm2_history.deposit_id = hm2_deposits.id and
              hm2_types.id = hm2_deposits.type_id and
              now() - interval hm2_types.hold day < hm2_history.date and
	      (hm2_history.type=\'earning\' or
		(hm2_history.type=\'deposit\' and (hm2_history.description like \'Compou%\' or hm2_history.description like \'<b>Archived transactions</b>:<br>Compound%\')))
	    group by hm2_history.ec
          ';
}

$sth = db_query($q);
$deps = [];
$deps[0] = 0;
while ($row = mysql_fetch_array($sth)) {
    array_push($hold, ['ec' => $row[ec], 'amount' => number_format($row[am], 2)]);
}

view_assign('hold', $hold);
view_assign('plans', $plans);
view_assign('qplans', sizeof($plans));
view_assign('min_deposit', sprintf('%0.2f', $min_deposit));
view_assign('compounding_available', $compounding_available);
view_execute('deposit.tpl');
