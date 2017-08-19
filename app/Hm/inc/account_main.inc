<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$id = $userinfo['id'];
  $q = 'select date_format(date + interval '.app('data')->settings['time_dif'].(''.' hour, \'%b-%e-%Y %r\') as last_login from user_access_log where user_id = '.$id.' order by id desc limit 1, 1');
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $last_access = $row['last_login'];
  }

  view_assign('last_access', $last_access);
  if (app('data')->settings[estimate_earnings_for_end_of_day] == 1) {
      $q = 'select * from deposits where user_id = '.$userinfo['id'];
      $sth = db_query($q);
      $est_earn = 0;
      while ($row = mysql_fetch_array($sth)) {
          $q = 'select sum(actual_amount) as sum from history where deposit_id = '.$row[id].' and type=\'earning\'';
          ($sth1 = db_query($q));
          while ($row1 = mysql_fetch_array($sth1)) {
              $est_earn += $row1[sum];
          }
      }

      $q = 'select deposits.*, to_days(now()) - to_days(last_pay_date) as cnt, types.period from deposits, types where types.id = deposits.type_id and user_id = '.$userinfo['id'].' and deposits.status=\'on\'';
      $sth = db_query($q);
      while ($row = mysql_fetch_array($sth)) {
          $q = 'select * from plans where parent='.$row[type_id].' and '.$row[actual_amount].' > min_deposit order by min_deposit desc limit 1';
          ($sth1 = db_query($q));
          while ($row1 = mysql_fetch_array($sth1)) {
              $d = 0;
              if ($row[period] == 'd') {
                  $d = 1;
              }

              if ($row[period] == 'w') {
                  $d = 7;
              }

              if ($row[period] == 'b-w') {
                  $d = 14;
              }

              if ($row[period] == 'm') {
                  $d = 31;
              }

              if ($row[period] == 'y') {
                  $d = 365;
              }

              if (0 < $d) {
                  $est_earn += $row[actual_amount] * $row[cnt] * $row1[percent] / (100 * $d);
                  continue;
              }
          }
      }

      view_assign('earned_est', number_format($est_earn, 2));
  }

  $ab = get_user_balance($id);
  $ab_formated = [];
  $ab['deposit'] = 0 - $ab['deposit'];
  $ab['earning'] = $ab['earning'];
  reset($ab);
  while (list($kk, $vv) = each($ab)) {
      $ab_formated[$kk] = number_format(abs($vv), 2);
  }

  view_assign('currency_sign', '$');
  view_assign('ab_formated', $ab_formated);
  $q = 'select count(*) as col, sum(amount) as actual_amount, ec from pending_deposits where user_id = '.$id.' and status != \'processed\' group by ec';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      app('data')->exchange_systems[$row['ec']]['pending_col'] = $row['col'];
      app('data')->exchange_systems[$row['ec']]['pending_amount'] = number_format($row['actual_amount'], 2);
  }

  $q = 'select sum(actual_amount) as sm, ec from history where user_id = '.$userinfo['id'].' group by ec';
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
  $id = $userinfo['id'];
  $q = 'select *, date_format(deposit_date + interval '.app('data')->settings['time_dif'].(''.' hour, \'%b-%e-%Y %r\') as dd from deposits where user_id = '.$id.' and status = \'on\' order by deposit_date desc limit 1');
  $sth = db_query($q);
  if ($row = mysql_fetch_array($sth)) {
      view_assign('last_deposit', number_format($row['amount'], 2));
      view_assign('last_deposit_date', $row['dd']);
  }

  $q = 'select *, date_format(date + interval '.app('data')->settings['time_dif'].(''.' hour, \'%b-%e-%Y %r\') as dd from history where user_id = '.$id.' and type = \'withdrawal\' order by date desc limit 1');
  $sth = db_query($q);
  if ($row = mysql_fetch_array($sth)) {
      view_assign('last_withdrawal', number_format(abs($row['actual_amount']), 2));
      view_assign('last_withdrawal_date', $row['dd']);
  }

  $q = 'select count(*) as col from wires where user_id = '.$id.' and status != \'processed\'';
  $sth = db_query($q);
  if ($row = mysql_fetch_array($sth)) {
      view_assign('wires', $row['col']);
  }

  view_execute('account_main.blade.php');
