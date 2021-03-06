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

$ok = 1;
  $amount = sprintf('%0.2f', app('data')->frm['amount']);
  $h_id = sprintf('%d', app('data')->frm['h_id']);
  $type = app('data')->frm['type'];
  $ec = sprintf('%d', substr(app('data')->frm['type'], 8));
  $on_hold = 0;
  if (app('data')->settings['allow_withdraw_when_deposit_ends'] == 1) {
      $q = 'select id from deposits where user_id = '.$userinfo['id'];
      $sth = db_query($q);
      $deps = [];
      $deps[0] = 0;
      while ($row = mysql_fetch_array($sth)) {
          array_push($deps, $row[id]);
      }

      $q = 'select sum(actual_amount) as amount from history where user_id = '.$userinfo['id'].(''.' and ec = '.$ec.' and
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
      $q = 'select sum(actual_amount) as amount from history where user_id = '.$userinfo['id'].(''.' and ec = '.$ec.' and
		deposit_id = '.$row[id].' and date > now() - interval '.$row[hold].' day and
			(type=\'earning\' or
		(type=\'deposit\' and (description like \'Compou%\' or description like \'<b>Archived transactions</b>:<br>Compound%\')));');
      ($sth1 = db_query($q));
      while ($row1 = mysql_fetch_array($sth1)) {
          $on_hold += $row1[amount];
      }
  }

  $q = 'select * from types where id = '.$h_id.' and closed = 0';
  $sth = db_query($q);
  $type = mysql_fetch_array($sth);
  $delay = -1;
  if (! $type) {
      view_assign('wrong_plan', 1);
      $ok = 0;
  } else {
      $plan_name = $type['name'];
      view_assign('plan_name', $plan_name);
      $delay += $type[delay];
  }

  if ($delay < 0) {
      $delay = 0;
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
          view_assign('wrong_plan', 1);
          $ok = 0;
      }

      if ($amount < $row['min']) {
          view_assign('less_than_min', 1);
          view_assign('min_amount', number_format($row['min'], 2));
          $ok = 0;
      }
  } else {
      view_assign('wrong_plan', 1);
      $ok = 0;
  }

  view_assign('type', app('data')->frm['type']);
  $q = 'select sum(actual_amount) as sm, ec from history where user_id = '.$userinfo['id'].' group by ec';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      app('data')->exchange_systems[$row['ec']]['balance'] = $row['sm'];
  }

  $accounting = get_user_balance($userinfo['id']);
  $max_deposit = $accounting['total'];
  $q = 'select min(max_deposit) as min, max(max_deposit) as max from plans where parent = '.$h_id;
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  if ($row) {
      if (0 < $row['min']) {
          if ($row['max'] < $accounting['total']) {
              $max_deposit = $row['max'];
          }
      }
  }

  $ps = substr(app('data')->frm['type'], 8);
  if (app('data')->exchange_systems[$ps]['balance'] < $amount + $on_hold) {
      if ($amount <= app('data')->exchange_systems[$ps]['balance']) {
          view_assign('on_hold', 1);
      } else {
          view_assign('not_enough_funds', 1);
      }

      $max_deposit = app('data')->exchange_systems[$ps]['balance'];
      $ok = 0;
  }

  if ($max_deposit < $amount) {
      view_assign('max_deposit_less', 1);
      view_assign('max_deposit_format', number_format($max_deposit, 2));
      $ok = 0;
  }

  view_assign('ps', app('data')->exchange_systems[$ps]['name']);
  if (($ok == 1 and app('data')->frm['action'] == 'confirm')) {
      $ec = $ps;
      $compound = sprintf('%.02f', app('data')->frm['compound']);
      if ($use_compound) {
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
      }

      $q = 'insert into deposits set
           user_id = '.$userinfo['id'].(''.',
           type_id = '.$h_id.',
           deposit_date = now(),
           last_pay_date = now() + interval '.$delay.' day,
           status = \'on\',
           amount = '.$amount.',
           actual_amount = '.$amount.',
           compound = '.$compound.',
           ec = '.$ec.'
    ');
      db_query($q);
      $deposit_id = mysql_insert_id();
      $q = 'insert into history set
           user_id = '.$userinfo['id'].(''.',
           amount = -'.$amount.',
           actual_amount = -'.$amount.',
           type=\'deposit\',
           date = now(),
           description = \'Deposit to '.$plan_name.'\',
           ec = '.$ec.',
           deposit_id = '.$deposit_id.'
    ');
      db_query($q);
      $user_id = $userinfo['id'];
      referral_commission($user_id, $amount, $ec);
      throw new RedirectException('/?a=deposit&say=deposit_success');
  }

  view_assign('ok', $ok);
  view_assign('h_id', $h_id);
  view_assign('amount', number_format($amount, 2));
  view_assign('famount', $amount);
  view_execute('deposit_account.confirm.blade.php');
