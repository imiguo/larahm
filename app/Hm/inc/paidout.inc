<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

app('data')->frm['month'] = intval(app('data')->frm['month']);
  if (app('data')->frm['month'] == 0) {
      app('data')->frm['month'] = date('n', time() + app('data')->settings['time_dif'] * 60 * 60);
  }

  app('data')->frm['year'] = intval(app('data')->frm['year']);
  if (app('data')->frm['year'] == 0) {
      app('data')->frm['year'] = date('Y', time() + app('data')->settings['time_dif'] * 60 * 60);
  }

  view_assign('frm', app('data')->frm);
  $month = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  view_assign('month', $month);
  $year = [];
  for ($i = app('data')->settings['site_start_year']; $i <= date('Y', time() + app('data')->settings['time_dif'] * 60 * 60); ++$i) {
      array_push($year, $i);
  }

  view_assign('year', $year);
  $datewhere = ' \''.app('data')->frm['year'].'\' = year(date + interval '.app('data')->settings['time_dif'].' hour) and '.'\''.app('data')->frm['month'].'\' = month(date + interval '.app('data')->settings['time_dif'].' hour) ';
  $type = 'withdrawal';
  $q = '
        select
              count(*) as cnt
        from
              hm2_history
        where
              '.$datewhere.' and type = \''.$type.'\' and user_id != 1
       ';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $count_all = $row['cnt'];
  $page = intval(app('data')->frm['page']);
  $onpage = 20;
  $colpages = ceil($count_all / $onpage);
  if ($page < 1) {
      $page = 1;
  }

  if (($colpages < $page and 1 < $colpages)) {
      $page = $colpages;
  }

  $from = ($page - 1) * $onpage;
  $q = 'select
              u.username,
              h.type,
              h.actual_amount,
              date_format(date + interval '.app('data')->settings['time_dif'].(''.' hour, \'%b-%e-%Y %r\') as dd
        from
              hm2_users as u left outer join hm2_history as h
                on u.id = h.user_id
        where '.$datewhere.' and h.type = \''.$type.'\' and user_id != 1
        order by h.id desc
        limit '.$from.', '.$onpage.'
       ');
  $sth = db_query($q);
  $stats = [];
  $total_withdraw = 0;
  while ($row = mysql_fetch_array($sth)) {
      $total_withdraw += abs($row['actual_amount']);
      $row['actual_amount'] = number_format(abs($row['actual_amount']), 2);
      array_push($stats, $row);
  }

  view_assign('stats', $stats);
  view_assign('total_withdraw', number_format($total_withdraw, 2));
  $pages = [];
  for ($i = 1; $i <= $colpages; ++$i) {
      $apage = [];
      $apage['page'] = $i;
      $apage['current'] = ($i == $page ? 1 : 0);
      array_push($pages, $apage);
  }

  view_assign('pages', $pages);
  view_assign('colpages', $colpages);
  view_assign('current_page', $page);
  if (1 < $page) {
      view_assign('prev_page', $page - 1);
  }

  if ($page < $colpages) {
      view_assign('next_page', $page + 1);
  }

  view_execute('paidout.blade.php');
