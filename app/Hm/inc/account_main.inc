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
  $q = 'select date_format(date + interval '.$settings['time_dif'].(''.' hour, \'%b-%e-%Y %r\') as last_login from hm2_user_access_log where user_id = '.$id.' order by id desc limit 1, 1');
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $last_access = $row['last_login'];
  }

  $smarty->assign('last_access', $last_access);
  if ($settings[estimate_earnings_for_end_of_day] == 1) {
      $q = 'select * from hm2_deposits where user_id = '.$userinfo['id'];
      $sth = db_query($q);
      $est_earn = 0;
      while ($row = mysql_fetch_array($sth)) {
          $q = 'select sum(actual_amount) as sum from hm2_history where deposit_id = '.$row[id].' and type=\'earning\'';
          ($sth1 = db_query($q));
          while ($row1 = mysql_fetch_array($sth1)) {
              $est_earn += $row1[sum];
          }
      }

      $q = 'select hm2_deposits.*, to_days(now()) - to_days(last_pay_date) as cnt, hm2_types.period from hm2_deposits, hm2_types where hm2_types.id = hm2_deposits.type_id and user_id = '.$userinfo['id'].' and hm2_deposits.status=\'on\'';
      $sth = db_query($q);
      while ($row = mysql_fetch_array($sth)) {
          $q = 'select * from hm2_plans where parent='.$row[type_id].' and '.$row[actual_amount].' > min_deposit order by min_deposit desc limit 1';
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

      $smarty->assign('earned_est', number_format($est_earn, 2));
  }

  $ab = get_user_balance($id);
  $ab_formated = [];
  $ab['deposit'] = 0 - $ab['deposit'];
  $ab['earning'] = $ab['earning'];
  reset($ab);
  while (list($kk, $vv) = each($ab)) {
      $ab_formated[$kk] = number_format(abs($vv), 2);
  }

  $smarty->assign('currency_sign', '$');
  $smarty->assign('ab_formated', $ab_formated);
  $q = 'select count(*) as col, sum(amount) as actual_amount, ec from hm2_pending_deposits where user_id = '.$id.' and status != \'processed\' group by ec';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $exchange_systems[$row['ec']]['pending_col'] = $row['col'];
      $exchange_systems[$row['ec']]['pending_amount'] = number_format($row['actual_amount'], 2);
  }

  $q = 'select sum(actual_amount) as sm, ec from hm2_history where user_id = '.$userinfo['id'].' group by ec';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $exchange_systems[$row['ec']]['balance'] = number_format($row['sm'], 2);
  }

  $ps = [];
  reset($exchange_systems);
  foreach ($exchange_systems as $id => $data) {
      array_push($ps, array_merge(['id' => $id], $data));
  }

  $smarty->assign('ps', $ps);
  $id = $userinfo['id'];
  $q = 'select *, date_format(deposit_date + interval '.$settings['time_dif'].(''.' hour, \'%b-%e-%Y %r\') as dd from hm2_deposits where user_id = '.$id.' and status = \'on\' order by deposit_date desc limit 1');
  $sth = db_query($q);
  if ($row = mysql_fetch_array($sth)) {
      $smarty->assign('last_deposit', number_format($row['amount'], 2));
      $smarty->assign('last_deposit_date', $row['dd']);
  }

  $q = 'select *, date_format(date + interval '.$settings['time_dif'].(''.' hour, \'%b-%e-%Y %r\') as dd from hm2_history where user_id = '.$id.' and type = \'withdrawal\' order by date desc limit 1');
  $sth = db_query($q);
  if ($row = mysql_fetch_array($sth)) {
      $smarty->assign('last_withdrawal', number_format(abs($row['actual_amount']), 2));
      $smarty->assign('last_withdrawal_date', $row['dd']);
  }

  $q = 'select count(*) as col from hm2_wires where user_id = '.$id.' and status != \'processed\'';
  $sth = db_query($q);
  if ($row = mysql_fetch_array($sth)) {
      $smarty->assign('wires', $row['col']);
  }

  $smarty->display('account_main.tpl');
