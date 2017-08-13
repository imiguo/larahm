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
               date_format(u.date_register + interval '.$settings['time_dif'].' hour, \'%b-%e-%Y %r\') as dd
         from 
               hm2_users as u left outer join hm2_history as h
                 on u.id = h.user_id
         where h.type = \'deposit\' and u.id != 1 and u.status = \'on\'
         group by
               u.username, dd
         order by balance asc
         limit 0, 10
        ';
  $sth = db_query($q);
  $stats = [];
  while ($row = mysql_fetch_array($sth)) {
      $row['balance'] = number_format(abs($row['balance']), 2);
      array_push($stats, $row);
  }

  $smarty->assign('top', $stats);
  $smarty->display('top10.tpl');
