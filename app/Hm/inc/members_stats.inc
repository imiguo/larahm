<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

function compare($a, $b)
{
    return strcmp($a['username'], $b['username']);
}

  $q = '
         select 
               count(distinct(user_id)) as cnt
         from 
               hm2_history 
         where 
               type in (\'deposit\', \'earning\', \'withdrawal\') and user_id != 1
        ';
  $q = 'select count(distinct(user_id)) as cnt
	from hm2_deposits';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $count_all = $row['cnt'];
  $q = 'select 
               u.username,
               h.type,
               sum(h.actual_amount) as amt
         from 
               hm2_users as u left outer join hm2_history as h
                 on u.id = h.user_id
         where h.type in (\'deposit\', \'earning\', \'withdrawal\') and user_id != 1
         group by
               h.type, u.username
        ';
  $sth = db_query($q);
  $stats = [];
  while ($row = mysql_fetch_array($sth)) {
      $stats[$row['username']][$row['type']] = $row['amt'];
  }

  $total = [];
  $astats = [];
  if ($stats) {
      foreach ($stats as $k => $row) {
          $row['username'] = $k;
          $total['deposit'] += abs($row['deposit']);
          $total['earning'] += abs($row['earning']);
          $total['withdrawal'] += abs($row['withdrawal']);
          $row['deposit'] = number_format(abs($row['deposit']), 2);
          $row['earning'] = number_format(abs($row['earning']), 2);
          $row['withdrawal'] = number_format(abs($row['withdrawal']), 2);
          array_push($astats, $row);
      }
  }

  $total['deposit'] = number_format($total['deposit'], 2);
  $total['earning'] = number_format($total['earning'], 2);
  $total['withdrawal'] = number_format($total['withdrawal'], 2);
  $smarty->assign('total', $total);
  usort($astats, compare);
  $page = $frm['page'];
  $onpage = 20;
  $colpages = ceil($count_all / $onpage);
  if ($page <= 1) {
      $page = 1;
  }

  if ($colpages < $page) {
      $page = $colpages;
  }

  $from = ($page - 1) * $onpage;
  $astats = array_slice($astats, $from, $onpage);
  $smarty->assign('stats', $astats);
  $pages = [];
  for ($i = 1; $i <= $colpages; ++$i) {
      $apage = [];
      $apage['page'] = $i;
      $apage['current'] = ($i == $page ? 1 : 0);
      array_push($pages, $apage);
  }

  $smarty->assign('pages', $pages);
  $smarty->assign('colpages', $colpages);
  $smarty->assign('current_page', $page);
  if (1 < $page) {
      $smarty->assign('prev_page', $page - 1);
  }

  if ($page < $colpages) {
      $smarty->assign('next_page', $page + 1);
  }

  $smarty->display('members_stats.tpl');
