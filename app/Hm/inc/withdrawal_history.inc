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
  $frm['day_to'] = sprintf('%d', $frm['day_to']);
  $frm['month_to'] = sprintf('%d', $frm['month_to']);
  $frm['year_to'] = sprintf('%d', $frm['year_to']);
  $frm['day_from'] = sprintf('%d', $frm['day_from']);
  $frm['month_from'] = sprintf('%d', $frm['month_from']);
  $frm['year_from'] = sprintf('%d', $frm['year_from']);
  if ($frm['day_to'] == 0) {
      $frm['day_to'] = date('j', time() + $settings['time_dif'] * 60 * 60);
      $frm['month_to'] = date('n', time() + $settings['time_dif'] * 60 * 60);
      $frm['year_to'] = date('Y', time() + $settings['time_dif'] * 60 * 60);
      $frm['day_from'] = 1;
      $frm['month_from'] = $frm['month_to'];
      $frm['year_from'] = $frm['year_to'];
  }

  $datewhere = '\''.$frm['year_from'].'-'.$frm['month_from'].'-'.$frm['day_from'].'\' + interval 0 day < date + interval '.$settings['time_dif'].' hour and '.'\''.$frm['year_to'].'-'.$frm['month_to'].'-'.$frm['day_to'].'\' + interval 1 day > date + interval '.$settings['time_dif'].' hour ';
  $typewhere = ' and type in (\'withdrawal\', \'withdraw_pending\')';
  $order = ($settings['use_history_balance_mode'] ? 'asc' : 'desc');
  $dformat = ($settings['use_history_balance_mode'] ? '%b-%e-%Y<br>%r' : '%b-%e-%Y %r');
  $q = 'select *, date_format(date + interval '.$settings['time_dif'].(''.' hour, \''.$dformat.'\') as d from hm2_history where '.$datewhere.' '.$typewhere.' and user_id = '.$id.' order by date '.$order.', id '.$order);
  $sth = db_query($q);
  $trans = [];
  while ($row = mysql_fetch_array($sth)) {
      $row['transtype'] = $transtype[$row['type']];
      $row['debitcredit'] = ($row['actual_amount'] < 0 ? 1 : 0);
      $row['orig_amount'] = $row['actual_amount'];
      $row['actual_amount'] = number_format(0 - $row['actual_amount'], 2);
      array_push($trans, $row);
  }

  if ($settings['use_history_balance_mode']) {
      $perioddebit = 0;
      $periodcredit = 0;
      for ($i = 0; $i < sizeof($trans); ++$i) {
          $start_id = $trans[$i]['id'];
          $q = 'select sum(actual_amount) as balance from hm2_history where id < '.$start_id.' and user_id = '.$userinfo['id'];
          $sth = db_query($q);
          $row = mysql_fetch_array($sth);
          $start_balance = $row['balance'];
          $trans[$i]['balance'] = number_format($start_balance + $trans[$i]['orig_amount'], 2);
      }

      $q = 'select
            sum(actual_amount * (actual_amount < 0)) as debit,
            sum(actual_amount * (actual_amount > 0)) as credit,
            sum(actual_amount) as balance
          from
            hm2_history where '.$datewhere.' '.$typewhere.' and user_id = '.$userinfo['id'];
      $sth = db_query($q);
      $row = mysql_fetch_array($sth);
      $start_balance = $row['balance'];
      $perioddebit = $row['debit'];
      $periodcredit = $row['credit'];
      $periodbalance = $row['balance'];
      $smarty->assign('perioddebit', number_format(abs($perioddebit), 2));
      $smarty->assign('periodcredit', number_format(abs($periodcredit), 2));
      $smarty->assign('periodbalance', number_format($periodbalance, 2));
      $q = 'select
            sum(actual_amount * (actual_amount < 0)) as debit,
            sum(actual_amount * (actual_amount > 0)) as credit,
            sum(actual_amount) as balance
          from
            hm2_history where 1=1 '.$typewhere.' and user_id = '.$userinfo['id'];
      $sth = db_query($q);
      $row = mysql_fetch_array($sth);
      $start_balance = $row['balance'];
      $perioddebit = $row['debit'];
      $periodcredit = $row['credit'];
      $periodbalance = $row['balance'];
      $smarty->assign('alldebit', number_format(abs($perioddebit), 2));
      $smarty->assign('allcredit', number_format(abs($periodcredit), 2));
      $smarty->assign('allbalance', number_format($periodbalance, 2));
  }

  $q = 'select sum(actual_amount) as periodsum from hm2_history where '.$datewhere.' and user_id = '.$id.' and type in (\'withdrawal\', \'withdraw_pending\')';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $periodsum = $row['periodsum'];
  $smarty->assign('periodsum', number_format(0 - $periodsum, 2));
  $q = 'select sum(actual_amount) as sum from hm2_history where 1=1 '.$userwhere.' and user_id = '.$id.' and type in (\'withdrawal\', \'withdraw_pending\')';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $allsum = $row['sum'];
  $smarty->assign('allsum', number_format(0 - $allsum, 2));
  $month = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  $smarty->assign('month', $month);
  $days = [];
  for ($i = 1; $i <= 31; ++$i) {
      array_push($days, $i);
  }

  $smarty->assign('day', $days);
  $year = [];
  for ($i = $settings['site_start_year']; $i <= date('Y', time() + $settings['time_dif'] * 60 * 60); ++$i) {
      array_push($year, $i);
  }

  $smarty->assign('year', $year);
  $smarty->assign('trans', $trans);
  $smarty->assign('qtrans', sizeof($trans));
  $smarty->assign('frm', $frm);
  $smarty->display('withdrawal_history.tpl');
