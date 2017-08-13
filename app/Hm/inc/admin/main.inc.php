<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$stats = [];
  $total_earned = 0;
  foreach ($exchange_systems as $id => $data) {
      if ($data['status'] == 1) {
          $q = 'select sum(actual_amount) as col from hm2_history where type=\'add_funds\' and to_days(now()) = to_days(date) and ec='.$id;
          $sth = db_query($q);
          $row = mysql_fetch_array($sth);
          $in[$id]['today'] = abs($row['col']);
          $in['total']['today'] += abs($row['col']);
          $q = 'select sum(actual_amount) as col from hm2_history where type=\'withdrawal\' and to_days(now()) = to_days(date) and ec='.$id;
          $sth = db_query($q);
          $row = mysql_fetch_array($sth);
          $out[$id]['today'] = abs($row['col']);
          $out['total']['today'] += abs($row['col']);
          $q = 'select sum(actual_amount) as col from hm2_history where type=\'add_funds\' and yearweek(now()) = yearweek(date) and ec='.$id;
          $sth = db_query($q);
          $row = mysql_fetch_array($sth);
          $in[$id]['week'] = abs($row['col']);
          $in['total']['week'] += abs($row['col']);
          $q = 'select sum(actual_amount) as col from hm2_history where type=\'withdrawal\' and yearweek(now()) = yearweek(date) and ec='.$id;
          $sth = db_query($q);
          $row = mysql_fetch_array($sth);
          $out[$id]['week'] = abs($row['col']);
          $out['total']['week'] += abs($row['col']);
          $q = 'select sum(actual_amount) as col from hm2_history where type=\'add_funds\' and EXTRACT(YEAR_MONTH FROM now()) = EXTRACT(YEAR_MONTH FROM date) and ec='.$id;
          $sth = db_query($q);
          $row = mysql_fetch_array($sth);
          $in[$id]['month'] = abs($row['col']);
          $in['total']['month'] += abs($row['col']);
          $q = 'select sum(actual_amount) as col from hm2_history where type=\'withdrawal\' and EXTRACT(YEAR_MONTH FROM now()) = EXTRACT(YEAR_MONTH FROM date) and ec='.$id;
          $sth = db_query($q);
          $row = mysql_fetch_array($sth);
          $out[$id]['month'] = abs($row['col']);
          $out['total']['month'] += abs($row['col']);
          $q = 'select sum(actual_amount) as col from hm2_history where type=\'add_funds\' and year(now()) = year(date) and ec='.$id;
          $sth = db_query($q);
          $row = mysql_fetch_array($sth);
          $in[$id]['year'] = abs($row['col']);
          $in['total']['year'] += abs($row['col']);
          $q = 'select sum(actual_amount) as col from hm2_history where type=\'withdrawal\' and year(now()) = year(date) and ec='.$id;
          $sth = db_query($q);
          $row = mysql_fetch_array($sth);
          $out[$id]['year'] = abs($row['col']);
          $out['total']['year'] += abs($row['col']);
          $q = 'select sum(actual_amount) as col from hm2_history where type=\'add_funds\' and ec='.$id;
          $sth = db_query($q);
          $row = mysql_fetch_array($sth);
          $in[$id]['total'] = abs($row['col']);
          $in['total']['total'] += abs($row['col']);
          $q = 'select sum(actual_amount) as col from hm2_history where type=\'withdrawal\' and ec='.$id;
          $sth = db_query($q);
          $row = mysql_fetch_array($sth);
          $out[$id]['total'] = abs($row['col']);
          $total_earned += $in[$id]['total'] - $out[$id]['total'];
          $out['total']['total'] += abs($row['col']);
          continue;
      }
  }

  $q = 'select count(*) as col from hm2_users where id <> 1';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $members_all = $row['col'];
  $q = 'select count(distinct user_id) as col from hm2_deposits';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $members_q_deposits = $row['col'];
  $q = 'select count(*) as col from hm2_users where id <> 1 and status = \'on\'';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $members_active = $row['col'];
  $q = 'select sum(status = \'on\') as col1, count(*) as col2 from hm2_types';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $types_active = $row['col1'];
  $types_all = $row['col2'];
  $q = 'select sum(actual_amount) as col from hm2_history';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $total_amount = $row['col'];
  $q = 'select sum(actual_amount) as col from hm2_deposits';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $total_deposits = $row['col'];
  $q = 'select sum(actual_amount) as col from hm2_deposits where status=\'on\'';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $current_deposits = $row['col'];
  $q = 'select sum(actual_amount) as col from hm2_history where type=\'withdrawal\'';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $total_withdraw = $row['col'];
  $q = 'select sum(actual_amount) as col from hm2_history where type=\'withdraw_pending\'';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $pending_withdraw = abs($row['col']);
  $q = 'select sum(actual_amount) as col from hm2_history where type=\'commissions\'';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $total_comissions = abs($row['col']);
  echo '
<b>Information</b><br>
Members:
	<a href="javascript:alert(\'How many users are registered in your system.\')" class=hlp>All: ';
  echo $members_all;
  echo '</a>,
	<a href="javascript:alert(\'How many active users does your system contain.\')" class=hlp>Active ';
  echo $members_active;
  echo '</a>,
	<a href="javascript:alert(\'How many users are disabled.\\n(cannot login and cannot earn any funds from principal.)\')" class=hlp>Disabled ';
  echo $members_all - $members_active;
  echo '</a><br>
Members: <a href="javascript:alert(\'How many users have ever made a deposit.\')" class=hlp>Made
a deposit ';
  echo $members_q_deposits;
  echo '</a>, <a href="javascript:alert(\'How many registered users haven\\\'t made a deposit in your system.\')" class=hlp>Have
not made a deposit: ';
  echo $members_all - $members_q_deposits;
  echo '</a><br>
<br>
Investment Packages:
	<a href="javascript:alert(\'Active investment packages number.\\nActive users earn if they have deposited funds in these packages.\')" class=hlp>Active ';
  echo $types_active;
  echo '</a>,
	<a href="javascript:alert(\'Inactive investment packages number.\\nUsers cannot invest money to these packages and cannot receive any earnings from these packages either.\')" class=hlp>Inactive ';
  echo $types_all - $types_active;
  echo '</a><br>
<br>
<a href="javascript:alert(\'The difference between the funds arrived from e-gold and all the withdrawals you\\\'ve made.\')" class=hlp>Total System Earnings:</a>	$';
  echo number_format($total_earned, 2);
  echo '<br>
<br>
<a href="javascript:alert(\'The sum of all users\' earnings and bonuses minus penalties and withdrawals.\')" class=hlp>Total
Members\' balance:</a> $';
  echo number_format($total_amount, 2);
  echo '<br>
<a href="javascript:alert(\'Total members\\\' deposit shows you how much funds have users deposited in your system total.\')" class=hlp>Total Members\' deposit:</a>
$';
  echo number_format($total_deposits, 2);
  echo '<br>
<a href="javascript:alert(\'The total principal of all users.\')" class=hlp>Current Members\' deposit:</a>
$';
  echo number_format($current_deposits, 2);
  echo '<br>
<a href="javascript:alert(\'The total referral commissions of all users.\')" class=hlp>Total Referrals Commissions:</a>
$';
  echo number_format($total_comissions, 2);
  echo '<br>

<br>
<a href="javascript:alert(\'All the funds you have ever withdrawn to users\\\' e-gold accounts.\')" class=hlp>Total
withdrawals:</a> $';
  echo number_format(0 - $total_withdraw, 2);
  echo '<br>
<a href="javascript:alert(\'The funds users requested to withdraw.\')" class=hlp>Pending withdrawals:</a>
$';
  echo number_format($pending_withdraw, 2);
  echo '<br>
<br><br>
';
  foreach ($exchange_systems as $id => $data) {
      if ($data['status'] != 1) {
          continue;
      }

      echo '<a href="javascript:alert(\'';
      echo $data['name'];
      echo ' in/out stats shows you how much funds users entered in your system and how much funds you withdrew today, this week, this month, this year and total.\')" class=hlp><b>';
      echo $data['name'];
      echo ' in/out</b></a><br><br>
<table cellspacing=0 cellpadding=2 border=0 width=100%>
<tr>
 <th bgcolor=FFEA00 colspan=2>Today</td>
 <th bgcolor=FFEA00 colspan=2>This Week</td>
 <th bgcolor=FFEA00 colspan=2>This Month</td>
 <th bgcolor=FFEA00 colspan=2>This Year</td>
 <th bgcolor=FFEA00 colspan=2>Total</td>
</tr><tr>
 <th>In</th>
 <th>Out</th>
 <th>In</th>
 <th>Out</th>
 <th>In</th>
 <th>Out</th>
 <th>In</th>
 <th>Out</th>
 <th>In</th>
 <t';
      echo 'h>Out</th>
</tr>
</tr><tr>
 <td align=right>';
      echo '<s';
      echo 'mall>$';
      echo number_format($in[$id]['today'], 2);
      echo '</small></td>
 <td align=right>';
      echo '<s';
      echo 'mall>$';
      echo number_format($out[$id]['today'], 2);
      echo '</small></td>
 <td align=right>';
      echo '<s';
      echo 'mall>$';
      echo number_format($in[$id]['week'], 2);
      echo '</small></td>
 <td align=right>';
      echo '<s';
      echo 'mall>$';
      echo number_format($out[$id]['week'], 2);
      echo '</small></td>
 <td align=right>';
      echo '<s';
      echo 'mall>$';
      echo number_format($in[$id]['month'], 2);
      echo '</small></td>
 <td align=right>';
      echo '<s';
      echo 'mall>$';
      echo number_format($out[$id]['month'], 2);
      echo '</small></td>
 <td align=right>';
      echo '<s';
      echo 'mall>$';
      echo number_format($in[$id]['year'], 2);
      echo '</small></td>
 <td align=right>';
      echo '<s';
      echo 'mall>$';
      echo number_format($out[$id]['year'], 2);
      echo '</small></td>
 <td align=right>';
      echo '<s';
      echo 'mall>$';
      echo number_format($in[$id]['total'], 2);
      echo '</small></td>
 <td align=right>';
      echo '<s';
      echo 'mall>$';
      echo number_format($out[$id]['total'], 2);
      echo '</small></td>
</tr><tr>
 <th colspan=2 >';
      echo '<s';
      echo 'mall>$';
      echo number_format($in[$id]['today'] - $out[$id]['today'], 2);
      echo '</small></th>
 <th colspan=2 >';
      echo '<s';
      echo 'mall>$';
      echo number_format($in[$id]['week'] - $out[$id]['week'], 2);
      echo '</small></th>
 <th colspan=2 >';
      echo '<s';
      echo 'mall>$';
      echo number_format($in[$id]['month'] - $out[$id]['month'], 2);
      echo '</small></th>
 <th colspan=2 >';
      echo '<s';
      echo 'mall>$';
      echo number_format($in[$id]['year'] - $out[$id]['year'], 2);
      echo '</small></th>
 <th colspan=2 >';
      echo '<s';
      echo 'mall>$';
      echo number_format($in[$id]['total'] - $out[$id]['total'], 2);
      echo '</small></th>
</tr>

</table>
<br><br>';
  }

  echo '<a href="javascript:alert(\'Total in/out stats shows you how much funds users entered in your system and how much funds you withdrew today, this week, this month, this year and total.\')" class=hlp><b>in/out
Total </b></a> <br>
<br>
<table cellspacing=0 cellpadding=2 border=0 width=100%>
<tr>
 <th bgcolor=FFEA00 colspan=2>Today</td>
 <th bgcolor=FFEA00 colspan=2>This Week</td>
 <th bgcolor=FFEA00 colspan=2>This';
  echo ' Month</td>
 <th bgcolor=FFEA00 colspan=2>This Year</td>
 <th bgcolor=FFEA00 colspan=2>Total</td>
</tr><tr>
 <th>In</th>
 <th>Out</th>
 <th>In</th>
 <th>Out</th>
 <th>In</th>
 <th>Out</th>
 <th>In</th>
 <th>Out</th>
 <th>In</th>
 <th>Out</th>
</tr>
</tr><tr>
 <td align=right>';
  echo '<s';
  echo 'mall>$';
  echo number_format($in['total']['today'], 2);
  echo '</small></td>
 <td align=right>';
  echo '<s';
  echo 'mall>$';
  echo number_format($out['total']['today'], 2);
  echo '</small></td>
 <td align=right>';
  echo '<s';
  echo 'mall>$';
  echo number_format($in['total']['week'], 2);
  echo '</small></td>
 <td align=right>';
  echo '<s';
  echo 'mall>$';
  echo number_format($out['total']['week'], 2);
  echo '</small></td>
 <td align=right>';
  echo '<s';
  echo 'mall>$';
  echo number_format($in['total']['month'], 2);
  echo '</small></td>
 <td align=right>';
  echo '<s';
  echo 'mall>$';
  echo number_format($out['total']['month'], 2);
  echo '</small></td>
 <td align=right>';
  echo '<s';
  echo 'mall>$';
  echo number_format($in['total']['year'], 2);
  echo '</small></td>
 <td align=right>';
  echo '<s';
  echo 'mall>$';
  echo number_format($out['total']['year'], 2);
  echo '</small></td>
 <td align=right>';
  echo '<s';
  echo 'mall>$';
  echo number_format($in['total']['total'], 2);
  echo '</small></td>
 <td align=right>';
  echo '<s';
  echo 'mall>$';
  echo number_format($out['total']['total'], 2);
  echo '</small></td>
</tr><tr>
 <th colspan=2 >';
  echo '<s';
  echo 'mall>$';
  echo number_format($in['total']['today'] - $out['total']['today'], 2);
  echo '</small></th>
 <th colspan=2 >';
  echo '<s';
  echo 'mall>$';
  echo number_format($in['total']['week'] - $out['total']['week'], 2);
  echo '</small></th>
 <th colspan=2 >';
  echo '<s';
  echo 'mall>$';
  echo number_format($in['total']['month'] - $out['total']['month'], 2);
  echo '</small></th>
 <th colspan=2 >';
  echo '<s';
  echo 'mall>$';
  echo number_format($in['total']['year'] - $out['total']['year'], 2);
  echo '</small></th>
 <th colspan=2 >';
  echo '<s';
  echo 'mall>$';
  echo number_format($in['total']['total'] - $out['total']['total'], 2);
  echo '</small></th>
</tr>

</table>
<br><br>';
  echo start_info_table('100%');
  echo 'Welcome to the HYIP Manager Admin Area!<br>
You can see help messages on almost all pages of the admin area in this part.<br>
<br>
You can see how many members are registered in the system on this page.<br>
System supports 3 types of users:<br>
<li>Active users. These users can login to the members area and receive earnings.</li>
<li>Suspended users. These users can login to the members area but will not ';
  echo 'receive
  any earnings.</li>
<li>Disabled users. These users can not login to the members area and will not
  receive any earnings.</li>
<br>
User becomes active when registering and only administrator can change status
of any registered user. You can see how many users are active and disabled in
the system at the top of this page. <br>
<br>

Investment packages:<br>
You can create unlimited sets of ';
  echo 'investment packages with any settings and payout options.
Also you can change status of any package.
<li> Active package. All active users will receive earnings every pay period if
  made a deposit</li>
<li> Inactive package. Users will not receive any earnings</li>
<br><br>
\'Total system earnings\' is a difference between funds came from e-gold and all the
withdrawals you made. <br>
<br>
\'Total member\'';
  echo 's balance\' shows you how many funds can users withdraw from the system.
It is the sum of all users\' earnings and bonuses minus penalties and withdrawals.
<br>
<br>
\'Total member\'s deposit\' shows you how many funds have users ever deposited in your system.
<br>
<br>
\'Current members\' deposit\' shows the overall users\' deposit. <br>
<br>
\'Total withdrawals\' shows you how many funds have you withdrawn to';
  echo ' users\' e-gold
accounts. <br>
<br>
\'Pending withdrawals\' shows you how many funds users have requested to withdraw.
<br>
<br>
E-gold in/out stats shows you how many funds users have entered in your system
and how many funds have you withdrawn today, this week, this month, this year
and total. ';
  echo end_info_table();
