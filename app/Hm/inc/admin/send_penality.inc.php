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
  $sth = db_query($q);
  ($row = mysql_fetch_array($sth));
  $all_c = $row['col'];
  $q = 'select count(*) as col from hm2_users, hm2_deposits where hm2_users.id > 1 and hm2_deposits.user_id = hm2_users.id group by hm2_users.id';
  $sth = db_query($q);
  ($row = mysql_fetch_array($sth));
  $act_c = sprintf('%d', $row['col']);
  $pas_c = $all_c - $act_c;
  echo '
<b>Send a penalty:</b><br><br>';
  if ($frm['say'] == 'wrongamount') {
      echo 'The penalty has not been sent. You had entered the wrong amount!<br>
<br>';
  }

  if ($frm['say'] == 'someerror') {
      echo 'The penalty has not been sent. Unknown error!<br>
<br>';
  }

  if ($frm['say'] == 'notsend') {
      echo 'The penalty has not been sent. No users found!<br>
<br>';
  }

  if ($frm['say'] == 'send') {
      echo 'The penalty has been sent. Total: $';
      echo number_format($frm['total'], 2);
      echo '<br>
<br>';
  }

  echo '
';
  echo '<s';
  echo 'cript language=javascript>
var u = Array (0, ';
  echo $all_c;
  echo ', ';
  echo $act_c;
  echo ', ';
  echo $pas_c;
  echo ');
function checkform() {
  if (document.formb.to.selectedIndex == 0) {
    if (document.formb.username.value == \'\') {
      alert("Please enter a username!");
      return false;
    }
  } else {
    return confirm("Are you sure you want to send the $"+document.formb.amount.value + " penalty to "+u[document.formb.to.selectedIndex]+" users = $"+document.formb.amount.value*u[document.formb.';
  echo 'to.selectedIndex]);
  }
  return true;
}
</script>

<form method=post onsubmit="return checkform();" name=formb>
<input type=hidden name=a value=send_penality>
<input type=hidden name=action value=send_penality>
<table cellspacing=0 cellpadding=2 border=0 width=100%><tr><td valign=top>
<table cellspacing=0 cellpadding=2 border=0>
<tr>
 <td>Amount (US$):</td>
 <td><input type=text name=amount value';
  echo '="100.00" class=inpts size=15 style="text-align: right;"></td>
</tr>
<tr>
 <td>E-currency:</td>
 <td>
	';
  echo '<s';
  echo 'elect name=ec class=inpts>';
  foreach ($exchange_systems as $id => $data) {
      if ($data['status'] != 1) {
          continue;
      }

      echo '	<option value="';
      echo $id;
      echo '">';
      echo $data['name'];
  }

  echo '	</select>
 </td>
</tr>
<tr>
 <td>Being sent to:</td>
 <td>
	';
  echo '<s';
  echo 'elect name=to class=inpts>
	<option value=user>One user (enter a username below)
	<option value=all>All users
	<option value=active>All users which have made a deposit
	<option value=passive>All users which haven\'t made a deposit
	</select>
 </td>
</tr><tr>
 <td>Username:</td>
 <td><input type=text name=username value="" class=inpts size=30></td>
</tr><tr>
 <td>Description:</td>
 <td><input type=text nam';
  echo 'e=description value="Enter the penalty description here." class=inpts size=30></td>
</tr><tr>
 <td>&nbsp;</td>
 <td><input type=submit value="Send" class=sbmt></td>
</tr></table>
</form>

</td><td valign=top align=center>';
  echo start_info_table('200');
  echo 'Send a penalty:<br>
You can send a penalty to one user, several users or all users.<br>
Enter an amount, a description and select a user or a user group you want send a penalty.<br>
User can read the description in the transactions history.<br>';
  echo end_info_table();
  echo '</td></tr></table>';
