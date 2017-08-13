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
  $ab = get_user_balance($id);
  $q = 'select count(*) as col from hm2_users where ref='.$userinfo[id];
  $sth = db_query($q);
  $q_affilates = 0;
  while ($row = mysql_fetch_array($sth)) {
      $q_affilates = $row['col'];
  }

  $q = 'select ec, sum(actual_amount) as sum from hm2_history where user_id = '.$id.' group by ec';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      $balance[$row['ec']] = $row['sum'];
  }

  echo '
<b>Manage user funds:</b><br><br>


<table cellspacing=0 cellpadding=2 border=0 width=100%><tr><td valign=top>
<table cellspacing=0 cellpadding=2 border=0>
<tr>
 <td>Account name:</td>
 <td>';
  echo $userinfo['username'];
  echo '</td>
</tr>
<tr>
 <td>User name:</td>
 <td>';
  echo $userinfo['name'];
  echo '</td>
</tr>
<tr>
 <td>E-mail:</td>
 <td><a href=\'mailto:';
  echo $userinfo['email'];
  echo '\'>';
  echo $userinfo['email'];
  echo '</td>
</tr>
<tr>
 <td>E-gold account no:</td>
 <td>';
  echo $userinfo['egold_account'];
  echo '</td>
</tr>
<tr>
 <td>Evocash account no:</td>
 <td>';
  echo $userinfo['evocash_account'];
  echo '</td>
</tr>
<tr>
 <td>IntGold account no:</td>
 <td>';
  echo $userinfo['intgold_account'];
  echo '</td>
</tr>
<tr>
 <td>StormPay account no:</td>
 <td>';
  echo $userinfo['stormpay_account'];
  echo '</td>
</tr>
<tr>
 <td>e-Bullion account no:</td>
 <td>';
  echo $userinfo['ebullion_account'];
  echo '</td>
</tr>
<tr>
 <td>PayPal account:</td>
 <td>';
  echo $userinfo['paypal_account'];
  echo '</td>
</tr>
<tr>
 <td>GoldMoney account no:</td>
 <td>';
  echo $userinfo['goldmoney_account'];
  echo '</td>
</tr>
<tr>
 <td>eeeCurrency account no:</td>
 <td>';
  echo $userinfo['eeecurrency_account'];
  echo '</td>
</tr>
 <td>Pecunix account no:</td>
 <td>';
  echo $userinfo['pecunix_account'];
  echo '</td>
</tr>
<tr><td colspan=2>&nbsp;</td></tr>
<tr>
 <td>Account balance:</td>
 <td align=right>$';
  echo number_format($ab['total'], 2);
  echo '</td>
</tr>';
  foreach ($exchange_systems as $id => $data) {
      if ($data['status'] != 1) {
          continue;
      }

      echo '
<tr>
 <td>';
      echo $data['name'];
      echo ' balance:</td>
 <td align=right>$';
      echo number_format($balance[$id], 2);
      echo '</td>
</tr>';
  }

  echo '<tr>
          <td colspan=2> <a href=?a=thistory&u_id=';
  echo $userinfo['id'];
  echo '>';
  echo '<s';
  echo 'mall>[transactions
            history]</small></a><br>
            <br>
 </td>
</tr>

<tr>
 <td>Total deposit: </td>
 <td align=right>$';
  echo number_format(0 - $ab['deposit'], 2);
  echo '</td>
</tr><tr>
 <td>Total active deposit:</td>
 <td align=right>$';
  echo number_format($ab['active_deposit'], 2);
  echo '</td>
</tr><tr>
          <td colspan=2> ';
  echo '<s';
  echo 'mall><a href=?a=thistory&u_id=';
  echo $userinfo['id'];
  echo '&ttype=deposit>[transactions
            history]</a> &nbsp; <a href=?a=releasedeposits&u_id=';
  echo $userinfo['id'];
  echo '>[release
            a deposit]</a></small><br>
            <br>
 </td>
</tr>

<tr>
 <td>Total earning:</td>
 <td align=right>$';
  echo number_format($ab['earning'], 2);
  echo '</td>
</tr><tr>
          <td colspan=2> ';
  echo '<s';
  echo 'mall><a href=?a=thistory&u_id=';
  echo $userinfo['id'];
  echo '&ttype=earning>[earnings
            history]</a></small><br>
            <br>
 </td>
</tr>
<tr>
 <td>Total withdrawal:</td>
 <td align=right>$';
  echo number_format(abs($ab['withdrawal']), 2);
  echo '</td>
</tr><tr>
 <td>Requested withdrawals:</td>
 <td align=right>$';
  echo number_format(abs($ab['withdraw_pending']), 2);
  echo '</td>
</tr><tr>
 <td colspan=2>
	';
  echo '<s';
  echo 'mall><a href=?a=thistory&u_id=';
  echo $userinfo['id'];
  echo '&ttype=withdrawal>[withdrawals history]</a> &nbsp; <a href=?a=thistory&u_id=';
  echo $userinfo['id'];
  echo '&ttype=withdraw_pending>[process withdrawals]</a></small><br><br>
 </td>
</tr>

<!--<tr>
 <td>Referral commissions:</td>
 <td align=right>$23.33!!!</td>
</tr><tr>
 <td colspan=2>
	';
  echo '<s';
  echo 'mall><a href=?a=thistory&u_id=';
  echo $userinfo['id'];
  echo '&ttype=commissions>[affilate history]</a></small><br><br>
 </td>
</tr>-->

<tr>
          <td>Total bonus:</td>
 <td align=right>$';
  echo number_format($ab['bonus'], 2);
  echo '</td>
</tr><tr>
          <td colspan=2> ';
  echo '<s';
  echo 'mall><a href=?a=thistory&u_id=';
  echo $userinfo['id'];
  echo '&ttype=bonus>[bonuses
            history]</a> &nbsp; <a href="?a=addbonuse&id=';
  echo $userinfo['id'];
  echo '">[add
            a bonus]</a></small><br>
            <br>
 </td>
</tr>

<tr>
          <td>Total penalty:</td>
 <td align=right>$';
  echo number_format(0 - $ab['penality'], 2);
  echo '</td>
</tr><tr>
          <td colspan=2> ';
  echo '<s';
  echo 'mall><a href=?a=thistory&u_id=';
  echo $userinfo['id'];
  echo '&ttype=penality>[penalties
            history]</a> &nbsp; <a href=?a=addpenality&id=';
  echo $userinfo['id'];
  echo '>[add
            a penalty]</a></small><br>
            <br>
 </td>
</tr>
<tr>
          <td>Referrals quantity:</td>
 <td align=right>';
  echo $q_affilates;
  echo '</td>
</tr><tr>
 <td colspan=2>
	';
  echo '<s';
  echo 'mall><a href=?a=affilates&u_id=';
  echo $userinfo['id'];
  echo '>[manage referrals]</small><br><br>
 </td>
</tr>
<tr>
 <td colspan=2>User IP\'s:</td>
</tr>
<tr>
 <td colspan=2>
  <table cellspacing=0 cellpadding=1 border=0 width=100%>
  <tr><th>IP</th><th>Last Access</th></tr>';
  $q = 'select date_format(max(date), \'%b-%e-%Y %r\') as fdate, max(date) + interval 0 hour as mdate, ip from hm2_user_access_log where user_id = '.$userinfo['id'].' group by ip order by mdate desc';
  $sth = db_query($q);
  while ($row = mysql_fetch_array($sth)) {
      echo '   <tr><td>';
      echo $row['ip'];
      echo ' &nbsp;</td><td>';
      echo $row['fdate'];
      echo '</td></tr>';
  }

  echo '  </table>
 </td>
</tr>
</table>

</td><td valign=top align=center>';
  echo start_info_table('230');
  echo 'Manage user funds:<br>
Account balance: how many funds can the user deposit to any investment package or withdraw from the system.<br>
Total deposit: how many funds has the user ever deposited to your system.<br>
Total active deposit: the whole current deposit of this user.<br>
Total earnings: how many funds has the user ever earned with your system.<br>
Total withdrawals: how many funds has the u';
  echo 'ser ever withdrawn from system.<br>
Total bonus: how many funds has the administrator ever added to the user account as a bonus.<br>
Total penalty: how many funds has the administrator ever deleted from the user account as a penalty.<br>

Actions:<br>
Transactions history - you can check the transactions history for this user.<br>
Active deposits/Transactions history - you can check the deposits ';
  echo 'history for this user.<br>
Earnings history - you can check the earnings history for this user.<br>
Withdrawals history - you can check the withdrawals history for this user.<br>
Process withdrawals - you can withdraw funds by clicking this link if a user asked you for a withdrawal.<br>
Bonuses history - you can check the bonuses history for this user.<br>
Penalties history - you can check the pen';
  echo 'alties history for this user.<br>
Add a bonus and add a penalty - add a bonus or a penalty to this user.<br>';
  echo end_info_table();
  echo '</td></tr></table>';
