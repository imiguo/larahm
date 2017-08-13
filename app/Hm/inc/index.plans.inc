<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$already_deposits = [];
  if (0 < $userinfo['id']) {
      $q = 'select type_id from hm2_deposits where user_id = '.$userinfo['id'];
      $sth = db_query($q);
      while ($row = mysql_fetch_array($sth)) {
          array_push($already_deposits, $row['type_id']);
      }
  }

  $q = 'select * from hm2_types where status = \'on\' order by id';
  $sth = db_query($q);
  $plans = [];
  while ($row = mysql_fetch_array($sth)) {
      if (0 < $userinfo['id']) {
          if (0 < $row['parent']) {
              if (!in_array($row['parent'], $already_deposits)) {
                  continue;
              }
          }
      }

      $q = 'select * from hm2_plans where parent = '.$row['id'].' order by id';
      if (!($sth1 = db_query($q))) {
      }

      $row['plans'] = [];
      while ($row1 = mysql_fetch_array($sth1)) {
          $row1['deposit'] = '';
          if ($row1['max_deposit'] == 0) {
              $row1['deposit'] = '$'.number_format($row1['min_deposit']).' and more';
          } else {
              $row1['deposit'] = '$'.number_format($row1['min_deposit']).' - $'.number_format($row1['max_deposit']);
          }

          array_push($row['plans'], $row1);
      }

      $periods = ['d' => 'Daily', 'w' => 'Weekly', 'b-w' => 'Bi Weekly', 'm' => 'Monthly', 'y' => 'Yearly'];
      $row['period'] = $periods[$row['period']];
      array_push($plans, $row);
  }

  $smarty->assign('index_plans', $plans);
