<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

header('Content-type: text/vnd.wap.wml');
  $q = 'select max(percent) as percent from hm2_referal';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $smarty->assign('percent', $row['percent']);
  }

  $smarty->display('wap/home.tpl');
