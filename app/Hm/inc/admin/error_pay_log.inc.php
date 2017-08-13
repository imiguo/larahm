<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

echo '
<b>Error transactions:</b><br><br>

<table cellspacing=1 cellpadding=2 border=0 width=100%>
<tr>
 <td bgcolor=FFEA00 align=center>Date</td>
 <td bgcolor=FFEA00 align=center>Error</td>
</tr>
';
  $q = 'select * from hm2_pay_errors order by id desc';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $txt = $row['txt'];
      $txt = preg_replace('/<.*?>/', '', $txt);
      echo '<tr>
 <td>';
      echo $row['date'];
      echo '</td>
 <td>';
      echo $txt;
      echo '</td>
</tr>
';
  }

  echo '</table>';
