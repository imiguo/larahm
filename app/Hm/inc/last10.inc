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
               h.actual_amount as balance,
               date_format(h.deposit_date + interval '.$settings['time_dif'].' hour, \'%b-%e-%Y %r\') as dd
         from 
               hm2_deposits as h left outer join hm2_users as u
                 on u.id = h.user_id
         where h.status = \'on\' and u.id != 1 and u.status = \'on\'
         order by deposit_date desc
         limit 0, 10
        ';
  $sth = db_query($q);
  $stats = [];
  while ($row = mysql_fetch_array($sth)) {
      $row['balance'] = number_format(abs($row['balance']), 2);
      array_push($stats, $row);
  }

  $smarty->assign('top', $stats);
  $smarty->display('last10.tpl');
