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
  app('data')->frm['day_to'] = sprintf('%d', app('data')->frm['day_to']);
  app('data')->frm['month_to'] = sprintf('%d', app('data')->frm['month_to']);
  app('data')->frm['year_to'] = sprintf('%d', app('data')->frm['year_to']);
  app('data')->frm['day_from'] = sprintf('%d', app('data')->frm['day_from']);
  app('data')->frm['month_from'] = sprintf('%d', app('data')->frm['month_from']);
  app('data')->frm['year_from'] = sprintf('%d', app('data')->frm['year_from']);
  if (app('data')->frm['day_to'] == 0) {
      app('data')->frm['day_to'] = date('j', time() + app('data')->settings['time_dif'] * 60 * 60);
      app('data')->frm['month_to'] = date('n', time() + app('data')->settings['time_dif'] * 60 * 60);
      app('data')->frm['year_to'] = date('Y', time() + app('data')->settings['time_dif'] * 60 * 60);
      app('data')->frm['day_from'] = 1;
      app('data')->frm['month_from'] = app('data')->frm['month_to'];
      app('data')->frm['year_from'] = app('data')->frm['year_to'];
  }

  $datewhere = '\''.app('data')->frm['year_from'].'-'.app('data')->frm['month_from'].'-'.app('data')->frm['day_from'].'\' + interval 0 day < date + interval '.app('data')->settings['time_dif'].' hour and '.'\''.app('data')->frm['year_to'].'-'.app('data')->frm['month_to'].'-'.app('data')->frm['day_to'].'\' + interval 1 day > date + interval '.app('data')->settings['time_dif'].' hour ';
  $typewhere = ' and type in (\'deposit\', \'early_deposit_release\', \'release_deposit\', \'early_deposit_charge\')';
  $order = (app('data')->settings['use_history_balance_mode'] ? 'asc' : 'desc');
  $dformat = (app('data')->settings['use_history_balance_mode'] ? '%b-%e-%Y<br>%r' : '%b-%e-%Y %r');
  $q = 'select *, date_format(date + interval '.app('data')->settings['time_dif'].(''.' hour, \''.$dformat.'\') as d from hm2_history where '.$datewhere.' '.$typewhere.' and user_id = '.$id.' order by date '.$order.', id '.$order);
  $sth = db_query($q);
  $trans = [];
  while ($row = mysql_fetch_array($sth)) {
      $row['transtype'] = $transtype[$row['type']];
      $row['debitcredit'] = ($row['actual_amount'] < 0 ? 1 : 0);
      $row['orig_amount'] = $row['actual_amount'];
      $row['actual_amount'] = number_format(abs($row['actual_amount']), 2);
      array_push($trans, $row);
  }

  if (app('data')->settings['use_history_balance_mode']) {
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
      view_assign('perioddebit', number_format(abs($perioddebit), 2));
      view_assign('periodcredit', number_format(abs($periodcredit), 2));
      view_assign('periodbalance', number_format($periodbalance, 2));
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
      view_assign('alldebit', number_format(abs($perioddebit), 2));
      view_assign('allcredit', number_format(abs($periodcredit), 2));
      view_assign('allbalance', number_format($periodbalance, 2));
  }

  $q = 'select sum(actual_amount) as periodsum from hm2_history where '.$datewhere.' and user_id = '.$id.' and type in (\'deposit\', \'early_deposit_release\', \'release_deposit\', \'early_deposit_charge\')';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $periodsum = $row['periodsum'];
  view_assign('periodsum', number_format(0 - $periodsum, 2));
  $q = 'select sum(actual_amount) as sum from hm2_history where 1=1 '.$userwhere.' and user_id = '.$id.' and type in (\'deposit\', \'early_deposit_release\', \'release_deposit\', \'early_deposit_charge\')';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $allsum = $row['sum'];
  view_assign('allsum', number_format(0 - $allsum, 2));
  $month = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  view_assign('month', $month);
  $days = [];
  for ($i = 1; $i <= 31; ++$i) {
      array_push($days, $i);
  }

  view_assign('day', $days);
  $year = [];
  for ($i = app('data')->settings['site_start_year']; $i <= date('Y', time() + app('data')->settings['time_dif'] * 60 * 60); ++$i) {
      array_push($year, $i);
  }

  view_assign('year', $year);
  view_assign('trans', $trans);
  view_assign('qtrans', sizeof($trans));
  view_assign('frm', app('data')->frm);
  view_execute('deposit_history.tpl');
