<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

header('Content-type: text/vnd.wap.wml');
  echo '<?xml version="1.0"?>';
  echo '<!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN" "http://www.wapforum.org/DTD/wml_1.1.xml">

<wml>
<card title="Global Statistics">
<p>';
  $q = 'select sum(actual_amount) as col from hm2_history where type=\'add_funds\' and to_days(now()) = to_days(date)';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $in['today'] = abs($row['col']);
  $q = 'select sum(actual_amount) as col from hm2_history where type=\'withdrawal\' and to_days(now()) = to_days(date)';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $out['today'] = abs($row['col']);
  $q = 'select sum(actual_amount) as col from hm2_history where type=\'add_funds\' and yearweek(now()) = yearweek(date)';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $in['week'] = abs($row['col']);
  $q = 'select sum(actual_amount) as col from hm2_history where type=\'withdrawal\' and yearweek(now()) = yearweek(date)';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $out['week'] = abs($row['col']);
  $q = 'select sum(actual_amount) as col from hm2_history where type=\'add_funds\' and EXTRACT(YEAR_MONTH FROM now()) = EXTRACT(YEAR_MONTH FROM date)';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $in['month'] = abs($row['col']);
  $q = 'select sum(actual_amount) as col from hm2_history where type=\'withdrawal\' and EXTRACT(YEAR_MONTH FROM now()) = EXTRACT(YEAR_MONTH FROM date)';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $out['month'] = abs($row['col']);
  $q = 'select sum(actual_amount) as col from hm2_history where type=\'add_funds\' and year(now()) = year(date)';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $in['year'] = abs($row['col']);
  $q = 'select sum(actual_amount) as col from hm2_history where type=\'withdrawal\' and year(now()) = year(date)';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $out['year'] = abs($row['col']);
  $q = 'select sum(actual_amount) as col from hm2_history where type=\'add_funds\'';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $in['total'] = abs($row['col']);
  $q = 'select sum(actual_amount) as col from hm2_history where type=\'withdrawal\'';
  $sth = db_query($q);
  $row = mysql_fetch_array($sth);
  $out['total'] = abs($row['col']);
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
  echo '
<b>Global Statistics:</b><br/>
Members: 
	All: ';
  echo $members_all;
  echo ', 
	Active ';
  echo $members_active;
  echo ', 
	Disabled ';
  echo $members_all - $members_active;
  echo '<br/>
Members: 
	Made deposit ';
  echo $members_q_deposits;
  echo ',
	Do not made deposit: ';
  echo $members_all - $members_q_deposits;
  echo '<br/>
<br/>
Investment Packages:
	Active ';
  echo $types_active;
  echo ',
	Inactive ';
  echo $types_all - $types_active;
  echo '<br/>
<br/>
Total System Earnings:	$';
  echo number_format($in['total'] - $out['total'], 2);
  echo '<br/>
<br/>
Total balance of Members: $';
  echo number_format($total_amount, 2);
  echo '<br/>
Total deposit of Members: $';
  echo number_format($total_deposits, 2);
  echo '<br/>
Current deposit of Members: $';
  echo number_format($current_deposits, 2);
  echo '<br/>

<br/>
Total withdraw: $';
  echo number_format(0 - $total_withdraw, 2);
  echo '<br/>
Pending withdraw: $';
  echo number_format($pending_withdraw, 2);
  echo '<br/>
<br/>
<a href="?a=admin_pending">Pending Withdraw</a><br/>
<a href="?a=logout">Logout</a>
</p>
</card>
</wml>';
