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
  if ($frm['say'] == 'done') {
      echo ' The penalty has been sent to the user.<br>
<br>';
  }

  echo '
<form method=post>
<input type=hidden name=a value=addpenality>
<input type=hidden name=action value=addpenality>
<input type=hidden name=id value=\'';
  echo $userinfo['id'];
  echo '\'>
  <b>Add a penalty:</b><br>
  <br>

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
 <td>Amount (US$):</td>
 <td><input type=text name=amount value="0.00" class=inpts size=10 style="text-align: right;"></td>
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
 <td>Description:</td>
 <td><input type=text name=desc value="Penality note" class=inpts size=30></td>
</tr><tr>
 <td colspan=2><input type=checkbox name=inform value=1 checked>
            Send the e-mail notification</td>
</tr><tr>
 <td>&nbsp;</td>
 <td><input type=submit value="Send penality" class=sbmt></td>
</tr></table>
</form>

</td>
<td valign=top align=center>
  ';
  echo start_info_table('200');
  echo '  Add a penalty:<br>
  To send a penalty to any user you should enter an amount and description of
  this penalty. User can read the description in the transactions history.<br>
  Check `send e-mail notification` to report the user about this penalty.
  ';
  echo end_info_table();
