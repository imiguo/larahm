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
  $types = [];
  $q = 'select * from hm2_types where status = \'on\'';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $types[$row['id']] = $row['name'];
  }

  $frm_env['HTTP_HOST'] = preg_replace('/www\\./', '', $frm_env['HTTP_HOST']);
  echo ' <b>Send a bonus:</b><br>
<br>';
  if ($frm['say'] == 'wrongamount') {
      echo 'Bonus has not been sent. You had entered the wrong amount!<br>
<br>';
  }

  if ($frm['say'] == 'someerror') {
      echo 'Bonus has not been sent. Unknown error!<br><br>';
  }

  if ($frm['say'] == 'notsend') {
      echo 'Bonus has not been sent. No users found!<br><br>';
  }

  if ($frm['say'] == 'send') {
      echo 'Bonus has been sent. Total: $';
      echo number_format($frm['total'], 2);
      echo '<br><br>';
  }

  if ($frm['say'] == 'invalid_code') {
      echo 'Bonus has been sent. Invalid confirmation code.<br><br>';
  }

  if ($frm['say'] == 'wrongplan') {
      echo 'Bonus has not been sent. Invalid Investment Plan selected.<br><br>';
  }

  echo '
';
  if ($frm['action'] == 'confirm') {
      echo '<form method=post name=formb>
<input type=hidden name=a value=send_bonuce>
<input type=hidden name=action value=send_bonuce>
<input type=hidden name=amount value="';
      echo $frm['amount'];
      echo '">
<input type=hidden name=ec value="';
      echo $frm['ec'];
      echo '">
<input type=hidden name=to value="';
      echo $frm['to'];
      echo '">
<input type=hidden name=username value="';
      echo $frm['username'];
      echo '">
<input type=hidden name=description value="';
      echo $frm['description'];
      echo '">
<input type=hidden name=deposit value="';
      echo $frm['deposit'];
      echo '">
<input type=hidden name=hyip_id value="';
      echo $frm['hyip_id'];
      echo '">
<table cellspacing=0 cellpadding=2 border=0 width=100%><tr><td valign=top>
<table cellspacing=0 cellpadding=2 border=0>
<tr>
 <td>Confirmation Code:</td>
 <td><input type=text name=code value="" class=inpts size=30></td>
</tr>
<tr>
 <td>&nbsp;</td>
 <td><input type=submit value="Confirm" class=sbmt></td>
</tr>
</table>';
  } else {
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
    return confirm("Are you sure you want to send $"+document.formb.amount.value + " to "+u[document.formb.to.selectedIndex]+" users = $"+document.formb.amount.value*u[document.formb.to.selectedI';
      echo 'ndex]);
  }
  return true;
}
</script>

<form method=post onsubmit="return checkform();" name=formb>
<input type=hidden name=a value=send_bonuce>
<input type=hidden name=action value=confirm>
<table cellspacing=0 cellpadding=2 border=0 width=100%><tr><td valign=top>
<table cellspacing=0 cellpadding=2 border=0>
<tr>
 <td nowrap>Amount (US$):</td>
 <td><input type=text name=amount value="100.00" cla';
      echo 'ss=inpts size=15 style="text-align: right;"></td>
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
 <td>Enter a username:</td>
 <td><input type=text name=username value="" class=inpts size=30></td>
</tr><tr>
 <td>Description:</td>
 <td><input type=';
      echo 'text name=description value="Enter the bonus description here." class=inpts size=30></td>
</tr><tr>
 <td colspan=2><input type=checkbox name=deposit value=1 onclick="document.formb.hyip_id.disabled = !this.checked"> Invest this Bonuse to plan:</td>
</tr><tr>
 <td>&nbsp;</td>
 <td>
  ';
      echo '<s';
      echo 'elect name=hyip_id class=inpts disabled>';
      foreach ($types as $id => $name) {
          echo '   <option value=';
          echo $id;
          echo '>';
          echo htmlspecialchars($name);
          echo '</option>';
      }

      echo '  </select>
 </td>
</tr><tr>
 <td colspan=2>';
      echo start_info_table();
      echo 'For security reason you will be asked confirmation code on next page. E-mail with confirmation code will be sent to account you enter bellow. E-mail account should be on \'';
      echo $frm_env['HTTP_HOST'];
      echo '\' domain.<br><br>
E-mail: <input type=text name=conf_email value="admin" class=inpts size=10>@';
      echo $frm_env['HTTP_HOST'];
      echo end_info_table();
      echo '</td>
</tr><tr>
 <td>&nbsp;</td>
 <td><input type=submit value="Send" class=sbmt></td>
</tr></table>
</form>
</td><td valign=top align=center>';
      echo start_info_table('200');
      echo 'Send a bonus:<br>
  You can send a bonus to one user, several users or all users.<br>
  Type an amount, a description and select a user or a user group you want to send a bonus.<br>
  User can read the description in the transactions history.<br>
';
      echo end_info_table();
      echo '</td></tr></table>';
  }
