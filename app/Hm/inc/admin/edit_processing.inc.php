<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$id = intval($frm['pid']);
  $q = 'select * from hm2_processings where id = '.$id;
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  if (!$row) {
      header('Location: ?a=processings');
      exit();
  }

  $fields = unserialize($row['infofields']);
  echo '
<b>Edit Processing:</b><br><br>';
  echo '<s';
  echo 'cript>
function c1()
{
  var d = document.processing;
  for (i = 1; i <= 5; i++)
  {
    d.elements[\'field[\'+i+\']\'].disabled = (d.elements[\'use[\'+i+\']\'].checked) ? 0 : 1;
  }
}
</script>
<form method=post name="processing">
<input type="hidden" name=a value=edit_processing>
<input type="hidden" name=action value=edit_processing>
<input type="hidden" name=pid value=';
  echo $row['id'];
  echo '>
<table cellspacing=0 cellpadding=1 border=0>
<tr>
 <td>Status</td>
 <td><input type="checkbox" name="status" value=1 ';
  echo $row[status] ? 'checked' : '';
  echo '></td>
</tr>
<tr>
 <td>Name:</td>
 <td><input type="text" name="name" value="';
  echo htmlspecialchars($row['name']);
  echo '" class=inpts size=40></td>
</tr>
<tr>
 <td width=117>Payment notes:</td>
 <td><textarea name="description" rows=8 cols=40 class=inpts>';
  echo htmlspecialchars($row['description']);
  echo '</textarea></td>
</tr>
</table>
<table cellspacing=0 cellpadding=2 border=0>
<tr>
 <td colspan=2><br>Information Fields:</td>
</tr>';
  for ($id = 1; $id <= count($fields); ++$id) {
      echo '<tr>
 <td><input type=checkbox name="use[';
      echo $id;
      echo ']" value=1 checked onclick="c1()"></td>
 <td>Field ';
      echo $id;
      echo ':</td>
 <td><input type="text" name="field[';
      echo $id;
      echo ']" value="';
      echo htmlspecialchars(stripslashes($fields[$id]));
      echo '" class=inpts size=40></td>
</tr>';
  }

  for ($id = count($fields) + 1; $id < count($fields) + 6; ++$id) {
      echo '<tr>
 <td><input type=checkbox name="use[';
      echo $id;
      echo ']" value=1 onclick="c1()"></td>
 <td>Field ';
      echo $id;
      echo ':</td>
 <td><input type="text" name="field[';
      echo $id;
      echo ']" value="" class=inpts size=40></td>
</tr>';
  }

  echo '</table>
<input type="submit" value="Update Processing" class=sbmt>
</form>';
  echo '<s';
  echo 'cript>
c1();
</script><br>';
  echo start_info_table('100%');
  echo 'Enter all the user instructions, your account number in this payment
system and all the needed information here. You\'ll see all new user
transactions in the "Pending deposits" section.<br>
You can also choose the fields a user has to fill after he has
transferred the funds to your account. You can ask the user to give
you the batch ID or his account number in the corresponding payment
system. This ';
  echo 'information will help you to easily find the transfer
or define whether it was really sent.';
  echo end_info_table();
