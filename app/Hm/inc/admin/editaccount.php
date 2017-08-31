<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

  $id = sprintf('%d', app('data')->frm['id']);
  $q = 'select * from users where id = '.$id.' and id <> 1';
  if (! ($sth = db_query($q))) {
  }

  $row = mysql_fetch_array($sth);

  echo '<b>Edit Member Account:</b><br>
<br>
';
  if (app('data')->frm['say'] == 'saved') {
      echo 'User information saved<br><br>';
  }

  if (app('data')->frm['say'] == 'incorrect_password') {
      echo 'Please check your password<br><br>';
  }

  if (app('data')->frm['say'] == 'incorrect_transaction_code') {
      echo 'Please check your transaction code<br><br>';
  }

  echo '
<form method=post name="regform">
<input type=hidden name=a value="editaccount">
<input type=hidden name=action value="editaccount">
<input type=hidden name=id value="';
  echo $id;
  echo '">
<table cellspacing=0 cellpadding=2 border=0 width=100%><tr><td valign=top>
<table cellspacing=0 cellpadding=2 border=0>
 <td>Full name:</td>
 <td><input type=text name=fullname value=\'';
  echo quote($row['name']);
  echo '\' class=inpts size=30></td>
</tr>';
  if (app('data')->settings['use_user_location']) {
      include app_path('Hm').'/inc/countries.php';
      echo '<tr>
 <td>Address:</td>
 <td><input type=text name=address value=\'';
      echo quote($row['address']);
      echo '\' class=inpts size=30></td>
</tr>
<tr>
 <td>City:</td>
 <td><input type=text name=city value=\'';
      echo quote($row['city']);
      echo '\' class=inpts size=30></td>
</tr>
<tr>
 <td>State:</td>
 <td><input type=text name=state value=\'';
      echo quote($row['state']);
      echo '\' class=inpts size=30></td>
</tr>
<tr>
 <td>Zip:</td>
 <td><input type=text name=zip value=\'';
      echo quote($row['zip']);
      echo '\' class=inpts size=30></td>
</tr>
<tr>
 <td>Country:</td>
 <td>
  ';
      echo '<s';
      echo 'elect name=country class=inpts>
   <option value=\'\'>--SELECT--</option>';
      foreach ($countries as $c) {
          echo '   <option ';
          echo $c['name'] == $row['country'] ? 'selected' : '';
          echo '>';
          echo quote($c['name']);
          echo '</option>';
      }

      echo '  </select>
 </td>
</tr>';
  }

  echo '<tr>
 <td>Username:</td>
 <td><input type=text name=username value=\'';
  echo quote($row['username']);
  echo '\' class=inpts size=30></td>
</tr>
<tr>
 <td>Password:</td>
 <td><input type=password name=password value="" class=inpts size=30></td>
</tr><tr>
 <td>Retype password:</td>
 <td><input type=password name=password2 value="" class=inpts size=30></td>
</tr>';
  if (app('data')->settings['use_transaction_code']) {
      echo '<tr>
 <td>Transaction Code:</td>
 <td><input type=password name=transaction_code value="" class=inpts size=30></td>
</tr><tr>
 <td>Retype Transaction Code:</td>
 <td><input type=password name=transaction_code2 value="" class=inpts size=30></td>
</tr>';
  }

  echo '<tr>
          <td>E-gold Account No:</td>
 <td><input type=text name=egold value=\'';
  echo quote($row['egold_account']);
  echo '\' class=inpts size=30></td>
</tr><tr>
 <!--td>Evocash Account No:</td>
 <td><input type=text name=evocash value=\'';
  echo quote($row['evocash_account']);
  echo '\' class=inpts size=30></td>
</tr><tr-->
 <td>INTGold Account No:</td>
 <td><input type=text name=intgold value=\'';
  echo quote($row['intgold_account']);
  echo '\' class=inpts size=30></td>
</tr><tr>
 <td>StormPay Account:</td>
 <td><input type=text name=stormpay value=\'';
  echo quote($row['stormpay_account']);
  echo '\' class=inpts size=30></td>
</tr><tr>
 <td>e-Bullion Account:</td>
 <td><input type=text name=ebullion value=\'';
  echo quote($row['ebullion_account']);
  echo '\' class=inpts size=30></td>
</tr><tr>
 <td>PayPal Account:</td>
 <td><input type=text name=paypal value=\'';
  echo quote($row['paypal_account']);
  echo '\' class=inpts size=30></td>
</tr><tr>
 <td>GoldMoney Account:</td>
 <td><input type=text name=goldmoney value=\'';
  echo quote($row['goldmoney_account']);
  echo '\' class=inpts size=30></td>
</tr><tr>
 <td>eeeCurrency Account:</td>
 <td><input type=text name=eeecurrency value=\'';
  echo quote($row['eeecurrency_account']);
  echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Pecunix Account:</td>
 <td><input type=text name=pecunix value=\'';
  echo quote($row['pecunix_account']);
  echo '\' class=inpts size=30></td>
</tr><tr>
 <td>E-mail address:</td>
 <td><input type=text name=email value=\'';
  echo quote($row['email']);
  echo '\' class=inpts size=30></td>
</tr><tr>
 <td>Status:</td>
 <td>';
  echo '<s';
  echo 'elect name=status class=inpts>
	<option value="on" ';
  echo $row['status'] == 'on' ? 'selected' : '';
  echo '>Active
	<option value="off" ';
  echo $row['status'] == 'off' ? 'selected' : '';
  echo '>Disabled
	<option value="suspended" ';
  echo $row['status'] == 'suspended' ? 'selected' : '';
  echo '>Suspended</select>
 </td>
</tr><tr>
 <td colspan=2><input type=checkbox name=auto_withdraw value=1 ';
  echo $row['auto_withdraw'] == 1 ? 'checked' : '';
  echo '>
            Auto-withdrawal enabled
            ';
  if (app('data')->settings['demomode'] == 1) {
      echo '            &nbsp; &nbsp; ';
      echo '<s';
      echo 'pan style="color: #D20202;">Checkbox is available
            in Pro version only</span>
            ';
  }

  echo '          </td>
</tr><tr>
 <td colspan=2><input type=checkbox name=admin_auto_pay_earning value=1 ';
  echo $row['admin_auto_pay_earning'] == 1 ? 'checked' : '';
  echo '>
            Tranfer earnings directly to the user\'s e-gold account
            ';
  if (app('data')->settings['demomode'] == 1) {
      echo '            &nbsp; &nbsp; ';
      echo '<s';
      echo 'pan style="color: #D20202;">Checkbox is available
            in Pro version only</span>
            ';
  }

  echo '          </td>
</tr><tr>';
  if ($row['came_from'] != '') {
      echo ' <td>Came from:</td>
 <td>';
      echo '<s';
      echo 'mall><a href="';
      echo $row['came_from'];
      echo '" target=_blank>';
      echo $row['came_from'];
      echo '</a></td>
</tr><tr>';
  }

  if ($row['activation_code'] != '') {
      echo ' <td colspan=2><input type=checkbox name=activate value=1> Activate acount. User account has been blocked by Brute Force Handler feature.</td>
</tr><tr>';
  }

  echo ' <td>&nbsp;</td>
 <td><input type=submit value="Save changes" class=sbmt></td>
</tr></table>
<input type="hidden" name="_token" value="'.csrf_token().'"></form>

</td><td valign=top>';
  echo start_info_table('200');
  echo 'Edit member:<br>
  You can change the user information and status here.
  ';
  echo end_info_table();
  echo '</td>
</tr></table>';
