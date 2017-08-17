<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

include app_path('Hm').'/inc/index.plans.inc';
  $q = 'select max(percent) as percent from hm2_referal';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      view_assign('percent', $row['percent']);
  }

  $ref_plans = [];
  $q = 'select * from hm2_referal order by from_value';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      array_push($ref_plans, $row);
  }

  view_assign('ref_plans', $ref_plans);
  $ref_levels = [];
  for ($l = 2; $l < 11; ++$l) {
      if ((0 < app('data')->settings['ref'.$l.'_cms'] and app('data')->settings['ref'.$l.'_cms'] < 100)) {
          array_push($ref_levels, ['level' => $l, 'percent' => app('data')->settings['ref'.$l.'_cms']]);
          continue;
      }
  }

  view_assign('ref_levels', $ref_levels);
  view_execute('invest_page.tpl');
