<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

if ($settings['demomode'] == 1) {
    echo start_info_table('100%');
    echo '<b>Demo version restriction!</b><br>
You cannot send newsletters!';
    echo end_info_table();
}

  echo '
';
  $q = 'select count(*) as col from hm2_users where id > 1';
  $sth = db_query($q);
  ($row = mysql_fetch_array($sth));
  $all_c = $row['col'];
  $q = 'select count(distinct user_id) as col from hm2_deposits';
  $sth = db_query($q);
  ($row = mysql_fetch_array($sth));
  $act_c = sprintf('%d', $row['col']);
  $pas_c = $all_c - $act_c;
  echo '
<b>Send a newsletter to users:</b><br><br>';
  if ($frm['say'] == 'someerror') {
      echo 'Message has not been sent. Unknown error!<br>
<br>';
  }

  if ($frm['say'] == 'notsend') {
      echo 'Message has not been sent. No users found!<br>
<br>';
  }

  if ($frm['say'] == 'send') {
      echo 'Message has been sent. Total: ';
      echo $frm['total'];
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
    return confirm("Are you sure you want to send the newsletter to "+u[document.formb.to.selectedIndex]+" users?");
  }
  return true;
}
</script>

<form method=post onsubmit="return check';
  echo 'form();" name=formb>
<input type=hidden name=a value=newsletter>
<input type=hidden name=action value=newsletter>
<table cellspacing=0 cellpadding=2 border=0>

<tr>
      <td>Being sent to:</td>
 <td>
	';
  echo '<s';
  echo 'elect name=to class=inpts>
	<option value=user>One user (type a username below)
	<option value=all>All users
	<option value=active>All users which have made a deposit
	<option value=passive>All users which haven\'t made a deposit
	</select>
 </td>
</tr><tr>
      <td>Username:</td>
 <td><input type=text name=username value="" class=inpts size=30></td>
</tr><tr>
 <td>Subject</td>
 <td><input type=text name';
  echo '=subject value="" class=inpts size=30 ></td>
</tr><tr>
      <td colspan=2>Enter your message here:</td>
</tr><tr>
 <td colspan=2><textarea name=description class=inpts cols=100 rows=20>
Hello #name#

Your account name is #username#
Your e-mail is #email#
Your e-gold account is #egold_account#
You have been registered: #date_register#
</textarea></td>
</tr><tr>
 <td>&nbsp;</td>
 <td><input type=submi';
  echo 't value="Send newsletter" class=sbmt></td>
</tr></table>
</form>

<br>';
  echo start_info_table('100%');
  echo 'Send a newsletter:<br>

This form helps you to send a newsletter to one or several users.<br>
Select a user or a user group, type a subject and a message text. Click on the \'send newsletter\' button once! It may take a time for a huge list.<br><br>

Personalization:<br>
You can use the following variables to personalize the newsletter:<br>
#name# - user first and last name<br>
#username# - user lo';
  echo 'gin<br>
#email# - user e-mail address<br>
#egold_account# - user e-gold account<br>
#date_register# - user registration date<br>';
  echo end_info_table();
