<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$id = sprintf('%d', $frm['id']);
  $q = 'select * from hm2_users where id = '.$id;
  $sth = db_query($q);
  $userinfo = mysql_fetch_array($sth);
  $frm_env['HTTP_HOST'] = preg_replace('/www\\./', '', $frm_env['HTTP_HOST']);
  $types = [];
  $q = 'select * from hm2_types where status = \'on\'';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $types[$row['id']] = $row['name'];
  }

  echo '
<b>Add a bonus:</b><br><br>
';
  if ($frm['say'] == 'done') {
      echo 'The bonus has been sent to the user.<br><br>';
  }

  if ($frm['say'] == 'invalid_code') {
      echo 'The bonus has been not sent to the user. Invalid confirmation code.<br><br>';
  }

  if ($frm['say'] == 'wrongplan') {
      echo 'Bonus has not been sent. Invalid Investment Plan selected.<br><br>';
  }

  echo '
';
  if ($frm['action'] == 'confirm') {
      echo '<form method=post name=formb>
<input type=hidden name=a value=addbonuse>
<input type=hidden name=action value=addbonuse>
<input type=hidden name=id value="';
      echo $id;
      echo '">
<input type=hidden name=amount value="';
      echo $frm['amount'];
      echo '">
<input type=hidden name=ec value="';
      echo $frm['ec'];
      echo '">
<input type=hidden name=desc value="';
      echo $frm['desc'];
      echo '">
<input type=hidden name=inform value="';
      echo $frm['inform'];
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
      echo '
<form method=post name=formb>
<input type=hidden name=a value=addbonuse>
<input type=hidden name=action value=confirm>
<input type=hidden name=id value=\'';
      echo $userinfo['id'];
      echo '\'>
<table cellspacing=0 cellpadding=2 border=0 width=100%><tr><td valign=top>
<table cellspacing=0 cellpadding=2 border=0>
<tr>
 <td>Account name:</td>
 <td>';
      echo $userinfo['username'];
      echo '</td>
</tr><tr>
 <td>User name:</td>
 <td>';
      echo $userinfo['name'];
      echo '</td>
</tr><tr>
 <td>User e-mail:</td>
 <td><a href=\'mailto:';
      echo $userinfo['email'];
      echo '\'>';
      echo $userinfo['email'];
      echo '</td>
</tr><tr>
          <td>E-gold account no:</td>
 <td>';
      echo $userinfo['egold_account'];
      echo '</td>
</tr><tr>
 <!--td>Evocash account no:</td>
 <td>';
      echo $userinfo['evocash_account'];
      echo '</td>
</tr><tr-->
 <td>INTGold account no:</td>
 <td>';
      echo $userinfo['intgold_account'];
      echo '</td>
</tr><tr>
 <td>StormPay account:</td>
 <td>';
      echo $userinfo['stormpay_account'];
      echo '</td>
</tr><tr>
 <td>e-Bullion account:</td>
 <td>';
      echo $userinfo['ebullion_account'];
      echo '</td>
</tr><tr>
 <td>PayPal account:</td>
 <td>';
      echo $userinfo['paypal_account'];
      echo '</td>
</tr><tr>
 <td>GoldMoney account:</td>
 <td>';
      echo $userinfo['goldmoney_account'];
      echo '</td>
</tr><tr>
 <td>eeeCurrency account:</td>
 <td>';
      echo $userinfo['eeecurrency_account'];
      echo '</td>
</tr><tr>
 <td>Pecunix account:</td>
 <td>';
      echo $userinfo['pecunix_account'];
      echo '</td>
</tr><tr>
 <td colspan=2>&nbsp;</td>
</tr><tr>
 <td>Select e-currency:</td>
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
</tr><tr>
 <td>Amount (US$):</td>
 <td><input type=text name=amount value="0.00" class=inpts size=10 style="text-align: right;"></td>
</tr><tr>
 <td>Description:</td>
 <td><input type=text name=desc value="Bonus note" class=inpts size=30></td>
</tr><tr>
 <td colspan=2><input type=checkbox name=inform value=1 checked> Send the e-mail notification</td>
</tr><tr>
 <td colspan=2><input type=checkb';
      echo 'ox name=deposit value=1 onclick="document.formb.hyip_id.disabled = !this.checked"> Invest this Bonuse to plan:</td>
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
      echo '</tr><tr>
</td>
 <td>&nbsp;</td>
 <td><input type=submit value="Send bonus" class=sbmt></td>
</tr></table>
</form>

</td>
<td valign=top align=center>
  ';
      echo start_info_table('200');
      echo '  Add a bonus:<br>
  To send a bonus to any user you should enter a bonus amount and description.
  The user can read the description in the transactions history.<br>
  Check `send e-mail notification` to report the user about this bonus.
  ';
      echo end_info_table();
  }
