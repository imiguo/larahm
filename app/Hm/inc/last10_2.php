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
               u.username,
               sum(h.actual_amount) as balance,
               date_format(u.date_register + interval '.app('data')->settings['time_dif'].' hour, \'%b-%e-%Y %r\') as dd
         from 
               users as u left outer join history as h
                 on u.id = h.user_id
         where h.type = \'deposit\' and u.id != 1 and u.status = \'on\'
         group by
               u.username, dd
         order by balance desc
         limit 0, 10
        ';
  $sth = db_query($q);
  $stats = [];
  while ($row = mysql_fetch_array($sth)) {
      $row['balance'] = number_format(abs($row['balance']), 2);
      array_push($stats, $row);
  }

  view_assign('top', $stats);
  view_execute('last10.blade.php');
