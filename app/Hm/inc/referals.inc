<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$smarty->assign('site_name', $settings['site_name']);
  $q = 'select count(*) as col from hm2_users where status = \'on\' and ref='.$userinfo['id'];
  $sth = db_query($q);
  $smarty->assign('total_ref', 0);
  while ($row = mysql_fetch_array($sth)) {
      $smarty->assign('total_ref', $row['col']);
  }

  $q = 'select count(distinct user_id) as col from hm2_users, hm2_deposits where ref = '.$userinfo['id'].' and hm2_deposits.user_id = hm2_users.id';
  $sth = db_query($q);
  $smarty->assign('active_ref', 0);
  while ($row = mysql_fetch_array($sth)) {
      $smarty->assign('active_ref', $row['col']);
  }

  $ab = get_user_balance($userinfo['id']);
  $smarty->assign('commissions', number_format($ab['commissions'], 2));
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

  $datewhere = '\''.$frm['year_from'].'-'.$frm['month_from'].'-'.$frm['day_from'].'\' + interval 0 day < date + interval '.$settings['time_dif'].' hour and '.'\''.$frm['year_to'].'-'.$frm['month_to'].'-'.$frm['day_to'].'\' + interval 1 day > date + interval '.$settings['time_dif'].' hour and';
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
  $smarty->assign('frm', $frm);
  $q = 'select *, date_format(date + interval '.$settings['time_dif'].(''.' hour, \'%b-%e-%Y\') as date from hm2_referal_stats where '.$datewhere.' user_id = ').$userinfo['id'];
  $sth = db_query($q);
  $refstat = [];
  while ($row = mysql_fetch_array($sth)) {
      array_push($refstat, $row);
      $smarty->assign('show_refstat', 1);
  }

  $smarty->assign('refstat', $refstat);
  $q_other_active = 0;
  $q_other = 0;
  $q = 'select * from hm2_users where ref = '.$userinfo['id'].' order by id desc';
  $sth = db_query($q);
  $referals = [];
  while ($row = mysql_fetch_array($sth)) {
      $q = 'select count(*) as col from hm2_deposits where user_id = '.$row['id'];
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
          $q = 'select id from hm2_users where ref in ('.$parents_string.')';
          $sth1 = db_query($q);
          $parents = [];
          while ($row1 = mysql_fetch_array($sth1)) {
              array_push($parents, $row1['id']);
              $q = 'select count(*) as col from hm2_deposits where user_id = '.$row1['id'];
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
      $smarty->assign('show_referals', 1);
  }

  $smarty->assign('referals', $referals);
  $smarty->assign('cnt_other_active', $q_other_active);
  $smarty->assign('cnt_other', $q_other);
  $q = 'select * from hm2_users where id = '.$userinfo['ref'];
  $sth = db_query($q);
  $row1 = mysql_fetch_array($sth);
  $upline = $row1;
  $smarty->assign('upline', $upline);
  $smarty->display('referals.tpl');
