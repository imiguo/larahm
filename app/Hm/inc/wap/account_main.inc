<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

header('Content-type: text/vnd.wap.wml');
  $id = $userinfo['id'];
  $q = 'select date_format(date + interval '.$settings['time_dif'].(''.' hour, \'%b-%e-%Y %r\') as last_login from hm2_user_access_log where user_id = '.$id.' order by id desc limit 1, 1');
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $last_access = $row['last_login'];
  }

  $smarty->assign('last_access', $last_access);
  $ab = get_user_balance($id);
  $ab_formated = [];
  $ab['deposit'] = 0 - $ab['deposit'];
  $ab['earning'] = number_format($ab['earning'], 2);
  reset($ab);
  while (list($kk, $vv) = each($ab)) {
      $ab_formated[$kk] = number_format($vv, 2);
  }

  $smarty->assign('ab_formated', $ab_formated);
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

  $smarty->display('wap/account_main.tpl');
