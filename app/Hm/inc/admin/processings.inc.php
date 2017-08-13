<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$processings = [];
  $q = 'select * from hm2_processings';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      array_push($processings, $row);
  }

  echo '<b>Custom processings:</b><br><br>

<form method=post>
<input type=hidden name=a value=update_processings>
<table cellpadding=1 cellspacing=1 border=0 width=100%>
<tr>
 <th bgcolor=FFEA00 width=1% nowrap>Status</th>
 <th bgcolor=FFEA00 width=99%>Name</th>
 <th bgcolor=FFEA00>Icon</th>
 <th bgcolor=FFEA00>Actions</th>
</tr>';
  if (0 < count($processings)) {
      for ($i = 0; $i < count($processings); ++$i) {
          echo '<tr>
<td align=center><input type=checkbox name="status[';
          echo $processings[$i]['id'];
          echo ']" value=1 ';
          echo $processings[$i]['status'] ? 'checked' : '';
          echo '></td>
<td>';
          echo $processings[$i]['name'];
          echo '</td>
<td align=center><img src=\'images/';
          echo $processings[$i]['id'];
          echo '.gif\' alt="Upload image \'';
          echo $processings[$i]['id'];
          echo '.gif\' to \'images\' folder" height=\'17\'></td>
<td nowrap><a href="?a=edit_processing&pid=';
          echo $processings[$i]['id'];
          echo '">[edit]</a> <a href="?a=delete_processing&pid=';
          echo $processings[$i]['id'];
          echo '" onclick="return confirm(\'Do youreally want to delete this processing?\')">[delete]</a></td>
</tr>';
      }
  } else {
      echo '<tr>
<td align=center colspan=4>No records found</td>
</tr>';
  }

  echo '</table><br>';
  if (0 < count($processings)) {
      echo '<input type="submit" value="Update" class=sbmt> &nbsp;';
  }

  echo '<input type="button" value="Add Processing" class=sbmt onclick="document.location=\'?a=add_processing\'">
</form>
<br>';
  echo start_info_table('100%');
  echo 'You can add or edit any payment processing in this section by clicking the "edit" or "add new" link.
You should provide the full instructions for a user to know how to make a
deposit using the specified payment system.<br><br>
Any processing you add can\'t allow users to deposit just by themselves.
The administrator has to approve or delete any transaction and
process all the users\' withdrawal reques';
  echo 'ts manually.';
  echo end_info_table();
