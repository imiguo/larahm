<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

if (($settings['demomode'] == 1 and $frm['type'] != '')) {
    echo start_info_table('100%');
    echo '<b>Demo version restriction!</b><br>
You cannot change change e-mail templates! ';
    echo end_info_table();
}

  echo '<b>Edit E-mail Templates:</b><br>
<br>
<form action="?" method=post>
<input type=hidden name="a" value="edit_emails">
<input type=hidden name="action" value="update_statuses">
<table cellspacing=1 cellpadding=2 border=0 width=100%>';
  $found = 0;
  $q = 'select id, name, status from hm2_emails';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      if ($row['id'] == $frm['type']) {
          $found = 1;
      }

      echo '<tr>
  <td width=1%><input type="checkbox" name="emails[';
      echo $row['id'];
      echo ']" value=1 ';
      echo $row['status'] == 1 ? 'checked' : '';
      echo '></td>
  <td width=99%><li>
  ';
      if ($row['id'] == $frm['type']) {
          echo '  <b>';
          echo $row['name'];
          echo '</b>
  ';
      } else {
          echo '  <a href="?a=edit_emails&type=';
          echo $row['id'];
          echo '">';
          echo $row['name'];
          echo '</a>
  ';
      }

      echo '</td></tr>';
  }

  echo '<tr>
 <td colspan=2><input type=submit value="Update" class=sbmt></td>
</tr>
</table>
</form>';
  if ($found) {
      if ($settings['demomode'] != 1) {
          if ($frm['action'] == 'save') {
              $subject = quote($frm['subject']);
              $text = quote($frm['text']);
              $text = preg_replace('/
/', '', $text);
              $q = 'update hm2_emails set subject=\''.$subject.'\', text=\''.$text.'\' where id=\''.$frm['type'].'\'';
              $sth = db_query($q);
              echo '<br><b>Template has been saved.</b></br>';
          }
      }

      $q = 'select * from hm2_emails where id = \''.$frm['type'].'\'';
      $sth = db_query($q);
      $row = mysql_fetch_array($sth);
      echo '<br><br>
<form method=post>
<input type=hidden name=a value=edit_emails>
<input type=hidden name=action value=save>
<input type=hidden name=type value=';
      echo $row['id'];
      echo '>
<table cellspacing=0 cellpadding=2 border=0>

<tr>
 <td>Subject:</td>
</tr>
<tr>
 <td>
  <input type="text" name="subject" value="';
      echo quote($row['subject']);
      echo '" class=inpts size=100>
 </td>
</tr>
<tr>
 <td>Message Template</td>
</tr>
<tr>
 <td>
  <textarea name=text class=inpts cols=100 rows=20>';
      echo quote($row['text']);
      echo '</textarea>
 </td>
</tr>
<tr>
 <td><input type=submit value="Save Changes" class=sbmt></td>
</tr></table>
</form>';
  }

  echo '
<br>';
  echo start_info_table('100%');
  if ($frm['type'] == '') {
      echo 'Select e-mail type to edit system messages.<br>
If checkbox opposite to template name is switched off e-mail will be not sent.';
  }

  echo '

';
  if ($frm['type'] == 'registration') {
      echo 'Users will receive this e-mail after registration.<br><br>

Personalization:<br>
#name# - first and last user name.<br>
#username# - user login<br>
#password# - user password<br>
#site_url# - your site url (check settings screen to set this variable)<br>
#site_name# - your site name (check settings screen to set this variable)<br><br>

*Password will be replased with ***** if you use double opt-in confirma';
      echo 'tion for user registration.';
  }

  echo '

';
  if ($frm['type'] == 'confirm_registration') {
      echo 'Users will receive this e-mail if you use double opt-in confirmation for user registration.<br><br>

Personalization:<br>
#name# - first and last user name.<br>
#site_url# - your site url (check settings screen to set this variable)<br>
#site_name# - your site name (check settings screen to set this variable)<br><br>

* Do not edit following part:<br>
#site_url#/?a=confirm_registration&c=#confirm_string#<b';
      echo 'r><br>
This string will be replaced with uniq confirmation url for every user.';
  }

  echo '

';
  if ($frm['type'] == 'forgot_password') {
      echo 'Users will receive this e-mail if forgot they password and request new password.<br><br>

Personalization:<br>
#name# - first and last user name.<br>
#username# - user login<br>
#password# - user password<br>
#site_url# - your site url (check settings screen to set this variable)<br>
#site_name# - your site name (check settings screen to set this variable)<br>
#ip# - IP address of visitor that requested p';
      echo 'assword.<br>';
  }

  echo '

';
  if ($frm['type'] == 'bonus') {
      echo 'Users will receive this e-mail if admin add deposit to they account and select checkbox \'send notification\'.<br><br>

Personalization:<br>
#name# - first and last user name.<br>
#amount# - bonus amount<br>
#site_url# - your site url (check settings screen to set this variable)<br>
#site_name# - your site name (check settings screen to set this variable)<br>';
  }

  echo '

';
  if ($frm['type'] == 'penalty') {
      echo 'Users will receive this e-mail if admin add penality to they account and select checkbox \'send notification\'.<br><br>

Personalization:<br>
#name# - first and last user name.<br>
#amount# - penality amount<br>
#site_url# - your site url (check settings screen to set this variable)<br>
#site_name# - your site name (check settings screen to set this variable)<br>';
  }

  echo '

';
  if ($frm['type'] == 'change_account') {
      echo 'Users will receive this e-mail after edit account information.<br><br>

Personalization:<br>
#name# - first and last user name.<br>
#email# - user e-mail address.<br>
#ip# - IP address of visitor that requested password.<br>
#egold#  - user egold account no.<br>
#password# - user password<br>

#site_url# - your site url (check settings screen to set this variable)<br>
#site_name# - your site name (check se';
      echo 'ttings screen to set this variable)<br>

';
  }

  if ($frm['type'] == 'withdraw_request_user_notification') {
      echo 'Users will receive this e-mail after withdraw request.<br><br>

Personalization:<br>
#username# - username.<br>
#name# - first and last user name.<br>
#amount#- withdraw amount.<br>
#ip# - IP address of user that requested withdraw.<br>

#site_url# - your site url (check settings screen to set this variable)<br>
#site_name# - your site name (check settings screen to set this variable)<br>

';
  }

  if ($frm['type'] == 'withdraw_request_admin_notification') {
      echo 'Administrator will receive this e-mail after user withdraw request.<br><br>

Personalization:<br>
#username# - username.<br>
#name# - first and last user name.<br>
#amount#- withdraw amount.<br>
#ip# - IP address of user that requested withdraw.<br>

#site_url# - your site url (check settings screen to set this variable)<br>
#site_name# - your site name (check settings screen to set this variable)<br>

';
  }

  if ($frm['type'] == 'withdraw_user_notification') {
      echo 'User will receive this e-mail after withdraw process. (After autopay if enabled, admin direct and mass withdraw processes)<br><br>

Personalization:<br>
#username# - username.<br>
#name# - first and last user name.<br>
#amount# - withdraw amount.<br>
#batch# - batch.<br>
#account# - user account.<br>
#currency# - payment currency.<br>

#site_url# - your site url (check settings screen to set this variable)';
      echo '<br>
#site_name# - your site name (check settings screen to set this variable)<br>

';
  }

  if ($frm['type'] == 'withdraw_admin_notification') {
      echo 'User will receive this e-mail after withdraw process autopay if enabled<br><br>

Personalization:<br>
#username# - username.<br>
#name# - first and last user name.<br>
#amount# - withdraw amount.<br>
#batch# - batch.<br>
#account# - user account.<br>
#currency# - payment currency.<br>

#site_url# - your site url (check settings screen to set this variable)<br>
#site_name# - your site name (check settings sc';
      echo 'reen to set this variable)<br>

';
  }

  if ($frm['type'] == 'deposit_admin_notification') {
      echo 'Administrator will receive this e-mail after user made deposit<br><br>

Personalization:<br>
#username# - username.<br>
#name# - first and last user name.<br>
#amount# - deposit amount.<br>
#batch# - batch.<br>
#account# - user account.<br>
#currency# - payment currency.<br>
#plan# - investment package name.<br>
#site_url# - your site url (check settings screen to set this variable)<br>
#site_name# - your si';
      echo 'te name (check settings screen to set this variable)<br>';
  }

  echo end_info_table();
