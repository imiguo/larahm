<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

view_assign('site_name', app('data')->settings['site_name']);
  $q = 'select count(*) as col from users where status = \'on\' and ref='.$userinfo['id'];
  $sth = db_query($q);
  view_assign('total_ref', 0);
  while ($row = mysql_fetch_array($sth)) {
      view_assign('total_ref', $row['col']);
  }

  $q = 'select count(distinct user_id) as col from users, deposits where ref = '.$userinfo['id'].' and deposits.user_id = users.id';
  $sth = db_query($q);
  view_assign('active_ref', 0);
  while ($row = mysql_fetch_array($sth)) {
      view_assign('active_ref', $row['col']);
  }

  $ab = get_user_balance($userinfo['id']);
  view_assign('commissions', number_format($ab['commissions'], 2));
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

  $datewhere = '\''.app('data')->frm['year_from'].'-'.app('data')->frm['month_from'].'-'.app('data')->frm['day_from'].'\' + interval 0 day < date + interval '.app('data')->settings['time_dif'].' hour and '.'\''.app('data')->frm['year_to'].'-'.app('data')->frm['month_to'].'-'.app('data')->frm['day_to'].'\' + interval 1 day > date + interval '.app('data')->settings['time_dif'].' hour and';
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
  view_assign('frm', app('data')->frm);
  $q = 'select *, date_format(date + interval '.app('data')->settings['time_dif'].(''.' hour, \'%b-%e-%Y\') as date from referal_stats where '.$datewhere.' user_id = ').$userinfo['id'];
  $sth = db_query($q);
  $refstat = [];
  while ($row = mysql_fetch_array($sth)) {
      array_push($refstat, $row);
      view_assign('show_refstat', 1);
  }

  view_assign('refstat', $refstat);
  $q_other_active = 0;
  $q_other = 0;
  $q = 'select * from users where ref = '.$userinfo['id'].' order by id desc';
  $sth = db_query($q);
  $referals = [];
  while ($row = mysql_fetch_array($sth)) {
      $q = 'select count(*) as col from deposits where user_id = '.$row['id'];
      ($sth2 = db_query($q));
      while ($row2 = mysql_fetch_array($sth2)) {
          $row[q_deposits] = $row2[col];
      }

      $parents = [$row['id']];
      $ref_stats = [];
      $i = 0;
      for ($i = 2; $i < 11; ++$i) {
          $parents_string = join(',', $parents);
          $q_active = 0;
          $q = 'select id from users where ref in ('.$parents_string.')';
          $sth1 = db_query($q);
          $parents = [];
          while ($row1 = mysql_fetch_array($sth1)) {
              array_push($parents, $row1['id']);
              $q = 'select count(*) as col from deposits where user_id = '.$row1['id'];
              ($sth2 = db_query($q));
              while ($row2 = mysql_fetch_array($sth2)) {
                  $q_deposits = $row2[col];
              }

              if (0 < $q_deposits) {
                  ++$q_other_active;
                  ++$q_active;
              }

              ++$q_other;
          }

          if (!$parents) {
              break;
          }

          array_push($ref_stats, ['level' => $i - 1, 'cnt' => sizeof($parents), 'cnt_active' => $q_active]);
      }

      $row['ref_stats'] = $ref_stats;
      array_push($referals, $row);
      view_assign('show_referals', 1);
  }

  view_assign('referals', $referals);
  view_assign('cnt_other_active', $q_other_active);
  view_assign('cnt_other', $q_other);
  $q = 'select * from users where id = '.$userinfo['ref'];
  $sth = db_query($q);
  $row1 = mysql_fetch_array($sth);
  $upline = $row1;
  view_assign('upline', $upline);
  view_execute('referals.blade.php');
