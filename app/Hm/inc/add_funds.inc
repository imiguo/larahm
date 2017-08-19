<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$ab = get_user_balance($userinfo['id']);
  $ab_formated = [];
  while (list($kk, $vv) = each($ab)) {
      $ab_formated[$kk] = number_format($vv, 2);
  }

  view_assign('ab_formated', $ab_formated);
  view_assign('frm', app('data')->frm);
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
  view_execute('add_funds.blade.php');
