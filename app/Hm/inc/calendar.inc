<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$id = intval($frm['type']);
  $plans = [];
  $q = 'select *,
               date_format(now() + interval '.$settings['time_dif'].' hour, \'%m/%d/%Y\') as from_date,
               date_format((now() + interval '.$settings['time_dif'].(''.' hour) + interval q_days day, \'%m/%d/%Y\') as to_date
        from hm2_types where id = '.$id);
  if (!($sth = db_query($q))) {
  }

  $trow = mysql_fetch_array($sth);
  if (!$trow) {
      $smarty->assign('error', 'type_not_found');
      $smarty->display('calendar_simple.tpl');
      exit();
  }

  $i = 0;
  $q = 'select * from hm2_plans where parent = '.$id.' order by id';
  if (!($sth = db_query($q))) {
  }

  while ($row = mysql_fetch_array($sth)) {
      $row['i'] = $i;
      ++$i;
      array_push($plans, $row);
  }

  $smarty->assign('plans', $plans);
  if (($trow['period'] == 'd' and $trow['work_week'])) {
      $trow['period'] = 'w-d';
  }

  $periods = ['w-d' => 'Work Days', 'd' => 'Days', 'w' => 'Weeks', 'b-w' => 'Bi Weeks', 'm' => 'Months', '2m' => 'Bi-Months', '3m' => '3 Months', '6m' => '6 Months', 'y' => 'Years'];
  $trow['period_name'] = $periods[$trow['period']];
  $trow['min_deposit'] = $plans[0][min_deposit];
  $smarty->assign('type', $trow);
  if ($trow['period'] == 'end') {
      $smarty->display('calendar_simple.tpl');
  } else {
      $smarty->display('calendar.tpl');
  }
