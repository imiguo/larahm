<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$q = 'select
               u1.username,
               u1.id,
               count(*) as col
         from
               users as u1 left outer join users as u2
                 on u1.id = u2.ref
         where u2.date_register > \''.app('data')->settings[refs10_start_date].'\' + interval '.app('data')->settings['time_dif'].' hour
         group by
               u1.username
         having col > 0
         order by col desc, u1.id
         limit 0, 20
        ';
  $sth = db_query($q);
  $stats = [];
  while ($row = mysql_fetch_array($sth)) {
      $q2 = 'select
                  count(distinct u.id) as col
            from
                  users as u,
                  deposits as d
            where
                  u.ref = '.$row[id].' and
                  u.id = d.user_id
           ';
      ($sth1 = db_query($q2));
      $row1 = mysql_fetch_array($sth1);
      $row[active_col] = $row1[col];
      array_push($stats, $row);
  }

  $q = 'select date_format(\''.app('data')->settings[refs10_start_date].'\' + interval '.app('data')->settings['time_dif'].' hour, \'%b-%e-%Y\') as d';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  view_assign('start_date', $row[d]);
  view_assign('stats', $stats);
  view_execute('refs10.blade.php');
