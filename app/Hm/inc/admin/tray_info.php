<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$q = 'select count(*) as col from hm2_users where id > 1';
  if (!($sth = db_query($q))) {
  }

  $qmembers = 0;
  while ($row = mysql_fetch_array($sth)) {
      $qmembers = $row['col'];
  }

  $q = 'select sum(actual_amount) as col from hm2_deposits where id > 1';
  $sth = db_query($q);
  $deposit = 0;
  while ($row = mysql_fetch_array($sth)) {
      $deposit = number_format(abs($row['col']), 2);
  }

  $q = 'select sum(actual_amount) as col from hm2_history where type=\'withdrawal\'';
  $sth = db_query($q);
  $withdraw = 0;
  while ($row = mysql_fetch_array($sth)) {
      $withdraw = number_format(abs($row['col']), 2);
  }

  echo 'Members: ';
  echo $qmembers;
  echo 'Deposits: $';
  echo $deposit;
  echo 'Withdrawals: $';
  echo $withdraw;
