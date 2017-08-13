<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

echo '<b>';
  echo $frm['type'] == 'problem' ? 'Problem' : 'New';
  echo ' Wire Transfers:</b><br><br>
<form method=post name=nform >

<table cellspacing=1 cellpadding=2 border=0 width=100%>
<tr>
 <th bgcolor=FFEA00>Account</th>
 <th bgcolor=FFEA00>Amount</th>
 <th bgcolor=FFEA00>Bank Name</th>
 <th bgcolor=FFEA00>Bank Account</th>
 <th bgcolor=FFEA00>-</th>
</tr>';
  if ($frm['type'] == 'problem') {
      $q = 'select hm2_wires.*, hm2_users.username from hm2_wires, hm2_users where hm2_wires.status=\'problem\' and hm2_users.id = hm2_wires.user_id order by wire_date desc';
  } else {
      $q = 'select hm2_wires.*, hm2_users.username from hm2_wires, hm2_users where hm2_wires.status=\'new\' and hm2_users.id = hm2_wires.user_id order by wire_date desc';
  }

  $sth = db_query($q);
  $col = 0;
  while ($row = mysql_fetch_array($sth)) {
      ++$col;
      echo '     <tr onMouseOver="bgColor=\'#FFECB0\';" onMouseOut="bgColor=\'\';">
	<td><b>';
      echo $row['username'];
      echo '</b></td>
	<td align=right>';
      echo number_format($row['amount'], 2);
      echo '</td>
	<td align=center>';
      echo $row['bname'];
      echo '</td>
	<td align=center>';
      echo $row['baccount'];
      echo '</td>
	<td align=center><a href=?a=wiredetails&id=';
      echo $row['id'];
      echo '>[details]</a></td>
     </tr>
    ';
  }

  if ($col == 0) {
      echo '       <tr><td colspan=5 align=center>No records found</td></tr>
    ';
  }

  echo '

</table>';
