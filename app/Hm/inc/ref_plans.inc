<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$q = 'select max(percent) as percent from hm2_referal';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $smarty->assign('percent', $row['percent']);
  }

  $ref_plans = [];
  $q = 'select * from hm2_referal order by from_value';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      array_push($ref_plans, $row);
  }

  $smarty->assign('ref_plans', $ref_plans);
  $ref_levels = [];
  for ($l = 2; $l < 11; ++$l) {
      if ((0 < $settings['ref'.$l.'_cms'] and $settings['ref'.$l.'_cms'] < 100)) {
          array_push($ref_levels, ['level' => $l, 'percent' => $settings['ref'.$l.'_cms']]);
          continue;
      }
  }

  $smarty->assign('ref_levels', $ref_levels);
  $smarty->display('ref_plans.tpl');
