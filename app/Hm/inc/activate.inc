<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$activation_code = quote($frm['code']);
  $code_found = 0;
  if ($activation_code != '') {
      $q = 'select id from hm2_users where activation_code = \''.$activation_code.'\'';
      $sth = db_query($q);
      while ($row = mysql_fetch_array($sth)) {
          $q = 'update hm2_users set bf_counter = 0, activation_code = \'\' where id = '.$row['id'];
          db_query($q);
          $code_found = 1;
      }
  }

  $smarty->assign('activated', $code_found);
  $smarty->display('activate.tpl');
