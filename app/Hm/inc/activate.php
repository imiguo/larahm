<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$activation_code = quote(app('data')->frm['code']);
  $code_found = 0;
  if ($activation_code != '') {
      $q = 'select id from users where activation_code = \''.$activation_code.'\'';
      $sth = db_query($q);
      while ($row = mysql_fetch_array($sth)) {
          $q = 'update users set bf_counter = 0, activation_code = \'\' where id = '.$row['id'];
          db_query($q);
          $code_found = 1;
      }
  }

  view_assign('activated', $code_found);
  view_execute('activate.blade.php');
