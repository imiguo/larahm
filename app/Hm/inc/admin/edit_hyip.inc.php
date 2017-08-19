<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use App\Exceptions\EmptyException;

$id = sprintf('%d', app('data')->frm['id']);
  $q = 'select * from types where id= '.$id;
  if (! ($sth = db_query($q))) {
  }

  $row = mysql_fetch_array($sth);
  if (! $row) {
      throw new EmptyException();
  }

  $q = 'select * from plans where parent = '.$id.' order by id';
  if (! ($sth = db_query($q))) {
  }

  $rates = [];
  while ($row1 = mysql_fetch_array($sth)) {
      array_push($rates, $row1);
  }

  $q = 'select * from types where status = \'on\' and id <> '.$id;
  if (! ($sth = db_query($q))) {
  }

  $packages = [];
  while ($row1 = mysql_fetch_array($sth)) {
      array_push($packages, $row1);
  }

  if (($id < 3 and app('data')->settings['demomode'] == 1)) {
      echo start_info_table('100%');
      echo '<b>Demo version restriction!</b><br>
You cannot edit this package. You should create a new package for edition. ';
      echo end_info_table();
  }

  echo '<s';
  echo 'cript language=javascript>
function checkform() {
  if (document.nform.hname.value==\'\') {
    alert("Please anter a HYIP name!");
    document.nform.hname.focus();
    return false;
  }

  return true;
}
</script>

<form method=post name=nform onsubmit="return checkform();">
<input type=hidden name=hyip_id value="';
  echo $id;
  echo '">
<table cellspacing=1 cellpadding=2 border=0 width=100%>
<tr>
 <td colspan=2 align=center><b>Edit `';
  echo $row['name'];
  echo '` Investment Package:</b></td>
</tr><tr>
 <td width=150><a href="javascript:alert(\'Enter your package name here.\')" class=hlp>Package Name</a></td>
 <td><input type=text name=hname class=inpts size=30 value=\'';
  echo quote($row['name']);
  echo '\'></td>
</tr><tr>
      <td><a href="javascript:alert(\'Specify your package duration here.\\nFor example 30 days, 365 days, or no limit.\')" class=hlp>Package
        Duration</a></td>
 <td>
	<input type=text name=hq_days class=inpts size=5 style="text-align:right" value="';
  echo quote($row['q_days']);
  echo '"> in days
	(<input type=checkbox name=hq_days_nolimit value=1 ';
  echo quote($row['q_days']) == 0 ? 'checked' : '';
  echo ' onclick="checkb()"> no limit)
 </td>
</tr><tr>
 <td><a href="javascript:alert(\'If the package is closed no users can deposit in it but all current deposits are working as usual.\')" class=hlp>Closed Package</a></td>
 <td>
	<input type=checkbox name=closed value=1 ';
  echo quote($row['closed']) == 1 ? 'checked' : '';
  echo '>
 </td>
</tr><tr>
 <td colspan=2>
<table cellspacing=0 cellpadding=2 border=0 width=100%><tr><td valign=top>
<table cellspacing=0 cellpadding=2 border=0 width=350>
<tr>
                  <td colspan=3><b>Specify the Rates:</b></td>
</tr><tr>
 <td align=center>#</td>
 <td align=center>Name</td>
 <td align=center>Min Amount</td>
 <td align=center>Max Amount</td>
 <td align=center>Percent</td>
</tr>';
  $i = 0;
  for ($i = 0; $i < count($rates); ++$i) {
      echo '<tr>
 <td>';
      echo $i + 1;
      echo '. <input type=checkbox name="rate_amount_active[';
      echo $i;
      echo ']" onclick="checkrates(';
      echo $i;
      echo ')" value=1 checked></td>
 <td><input type=text name="rate_amount_name[';
      echo $i;
      echo ']" value="';
      echo $rates[$i]['name'];
      echo '" class=inpts size=10></td>
 <td><input type=text name="rate_min_amount[';
      echo $i;
      echo ']" value="';
      echo $rates[$i]['min_deposit'];
      echo '" class=inpts size=10 style="text-align:right"></td>
 <td><input type=text name="rate_max_amount[';
      echo $i;
      echo ']" value="';
      echo $rates[$i]['max_deposit'] == 0 ? '' : $rates[$i]['max_deposit'];
      echo '" class=inpts size=10 style="text-align:right"></td>
 <td><input type=text name="rate_percent[';
      echo $i;
      echo ']" value="';
      echo $rates[$i]['percent'];
      echo '" class=inpts size=10 style="text-align:right"></td>
</tr>';
  }

  for ($j = $i + 1; $j - 5 <= $i; ++$j) {
      echo '<tr>
 <td>';
      echo $j;
      echo '. <input type=checkbox name="rate_amount_active[';
      echo $j - 1;
      echo ']" onclick="checkrates(';
      echo $j - 1;
      echo ')" value=1></td>
 <td><input type=text name="rate_amount_name[';
      echo $j - 1;
      echo ']" value="Plan ';
      echo $j;
      echo '" class=inpts size=10></td>
 <td><input type=text name="rate_min_amount[';
      echo $j - 1;
      echo ']" value="" class=inpts size=10 style="text-align:right"></td>
 <td><input type=text name="rate_max_amount[';
      echo $j - 1;
      echo ']" value="" class=inpts size=10 style="text-align:right"></td>
 <td><input type=text name="rate_percent[';
      echo $j - 1;
      echo ']" value="" class=inpts size=10 style="text-align:right"></td>
</tr>';
  }

  echo '</table>
</td><td valign=top>
<br><br><br>
              ';
  echo start_info_table('100%');
  echo '              Type 0 in the max amount field if you do not want to limit your
              users\' maximal deposit amount.
              ';
  echo end_info_table();
  echo '            </td>
          </tr></table>

 </td>
</tr><tr>
 <td colspan=2><b>Description:</b></td>
</tr><tr>
 <td colspan=2><textarea cols=80 rows=10 name=plan_description class=inpts>';
  echo $row['dsc'];
  echo '</textarea><br><br></td>
</tr><tr>

 <td><a href="javascript:alert(\'Specify here when user will get earning from deposit at this package\')" class=hlp>Payment period:</a></td>
 <td>
	';
  echo '<s';
  echo 'elect name=hperiod class=inpts onchange="CheckCompound();CalculateProfit();InitCalendar();">
		<option value="d" ';
  echo $row['period'] == 'd' ? 'selected' : '';
  echo '>Daily
		<option value="w" ';
  echo $row['period'] == 'w' ? 'selected' : '';
  echo '>Weekly
		<option value="b-w" ';
  echo $row['period'] == 'b-w' ? 'selected' : '';
  echo '>Bi-weekly
		<option value="m" ';
  echo $row['period'] == 'm' ? 'selected' : '';
  echo '>Monthly
		<option value="2m" ';
  echo $row['period'] == '2m' ? 'selected' : '';
  echo '>Every 2 months
		<option value="3m" ';
  echo $row['period'] == '3m' ? 'selected' : '';
  echo '>Every 3 months
		<option value="6m" ';
  echo $row['period'] == '6m' ? 'selected' : '';
  echo '>Every 6 months
		<option value="y" ';
  echo $row['period'] == 'y' ? 'selected' : '';
  echo '>Yearly
		<option value="end" ';
  echo $row['period'] == 'end' ? 'selected' : '';
  echo '>After the specified period</select>
 </td>
</tr><tr>
 <td><a href="javascript:alert(\'Users will receive earnings if the package status is active.\')" class=hlp>Status</td>
 <td>
	';
  echo '<s';
  echo 'elect name=hstatus class=inpts>
		<option value=\'on\' ';
  echo $row['status'] == 'on' ? 'selected' : '';
  echo '>Active
		<option value=\'off\' ';
  echo $row['status'] == 'off' ? 'selected' : '';
  echo '>Inactive</select>
 </td>
</tr><tr>
 <td colspan=2><input type=checkbox name=hreturn_profit value=1 ';
  echo $row['return_profit'] == 1 ? 'checked' : '';
  echo ' onclick="CalculateProfit();InitCalendar();"> <a href="javascript:alert(\'You can return the principal to user account when the package is finished.\')" class=hlp>Return principal after the plan completion</td>
</tr><tr>
 <td colspan=2><input type=checkbox name=use_compound value=1 ';
  echo $row['use_compound'] == 1 ? 'checked' : '';
  echo ' onclick="checkd();CalculateProfit();InitCalendar();"> <a href="javascript:alert(\'You can use the compounding for this package.\')" class=hlp>Use compounding</td>
</tr><tr>
 <td rowspan=2> &nbsp; Compounding deposit amount limits:</td>
 <td>min: <input type=input name=compound_min_deposit value="';
  echo $row['compound_min_deposit'];
  echo '" class=inpts size=6> max: <input type=input name=compound_max_deposit value="';
  echo $row['compound_max_deposit'];
  echo '" class=inpts size=6></td>
</tr><tr>
 <td>';
  echo '<s';
  echo 'mall>set 0 as max to skip limitation</small></td>
</tr><tr>
      <td colspan=2> &nbsp; Compounding percent limits:</td>
</tr><tr>
 <td> &nbsp; <input type=radio name=compound_percents_type value=0 ';
  echo $row['compound_percents_type'] == 0 ? 'checked' : '';
  echo ' onclick="checkd1()">
        Compounding percent:</td>
 <td>min: <input type=input name=compound_min_percent value="';
  echo $row['compound_min_percent'];
  echo '" class=inpts size=6> max: <input type=input name=compound_max_percent value="';
  echo $row['compound_max_percent'];
  echo '" class=inpts size=6></td>
</tr><tr>
 <td> &nbsp; <input type=radio name=compound_percents_type value=1 ';
  echo $row['compound_percents_type'] == 1 ? 'checked' : '';
  echo ' onclick="checkd1()">
        Compounding percent solid values:<br> &nbsp;  &nbsp;  &nbsp;  &nbsp;';
  echo '<s';
  echo 'mall>comma separated (ex: 0,30,50,70,100)</small></td>
 <td><input type=input name=compound_percents value="';
  echo $row['compound_percents'];
  echo '" class=inpts></td>
</tr><tr>
 <td colspan=2><input type=checkbox name=withdraw_principal value=1 ';
  echo $row['withdraw_principal'] == 1 ? 'checked' : '';
  echo ' onclick="checkc()"> <a href="javascript:alert(\'You can allow users to return principal to user account and withdraw it. You can define a fee for this transaction and minimal deposit duration.\')" class=hlp>Allow principal withdrawal.</td>
</tr><tr>
      <td> &nbsp; The principal withdrawal fee:</td>
 <td><input type=input name=withdraw_principal_percent value="';
  echo $row['withdraw_principal_percent'];
  echo '" class=inpts> %</td>
</tr><tr>
      <td> &nbsp; Enter the minimal deposit withdrawal duration:</td>
 <td><input type=input name=withdraw_principal_duration value="';
  echo $row['withdraw_principal_duration'];
  echo '" class=inpts> days</td>
</tr><tr>
      <td> &nbsp; Enter the maximal deposit withdrawal duration:<br></td>
 <td><input type=input name=withdraw_principal_duration_max value="';
  echo $row['withdraw_principal_duration_max'];
  echo '" class=inpts> days<br>';
  echo '<s';
  echo 'mall>set 0 to skip limitation</small></td>
</tr><tr>
 <td colspan=2><input type=checkbox name=\'work_week\' value=1 ';
  echo $row['work_week'] == 1 ? 'checked' : '';
  echo ' onclick="CalculateProfit();InitCalendar();"> <a href="javascript:alert(\'Earnings will accumulate on user accounts only  on Mon-Fri. Available for daily payment plans.\')" class=hlp>Earnings only on mon-fri</td>
</tr>';
  if (0 < count($packages)) {
      echo '<tr>
 <td colspan=2><input type=checkbox name=parentch value=1 ';
      echo $row['parent'] == 0 ? '' : 'checked';
      echo '> <a href="javascript:alert(\'Administrator can select a \\\'parent\\\' package. Then users should deposit to parent package before depositing to this one.\')" class=hlp>Allow depositing only after the user have deposited to the following package:</a> <br> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;';
      echo '<s';
      echo 'elect name=parent class=inpts><option value=0>Select
	';
      for ($i = 0; $i < count($packages); ++$i) {
          echo '	<option ';
          echo $row['parent'] == $packages[$i]['id'] ? 'selected' : '';
          echo ' value=';
          echo $packages[$i]['id'];
          echo '>';
          echo $packages[$i]['name'];
          echo '	';
      }

      echo '</select>
 </td>
</tr>
<tr>
 <td colspan=2>
	Hold earnings at account for <input type=text name=hold value=\'';
      echo $row['hold'];
      echo '\' class=inpts size=5> days after payout (set 0 for disable this feature)
 </td>
</tr>
<tr>
 <td colspan=2>
	Delay earning for <input type=text name=delay value=\'';
      echo $row[delay];
      echo '\' class=inpts size=5> days since deposit (set 0 for disable this feature)
 </td>
</tr>';
  }

  echo '</table>
<br>';
  echo '<s';
  echo 'cript language=javascript>
function CheckCompound() {
  if (document.nform.hperiod.selectedIndex == 5) {
    document.nform.use_compound.disabled = true;
  } else {
    document.nform.use_compound.disabled = false;
  }
  CheckDailyPlan();
}
function CheckDailyPlan() {
  if (document.nform.hperiod.selectedIndex == 0) {
    document.nform.work_week.disabled = false;
  } else {
    document.nform.wor';
  echo 'k_week.disabled = true;
  }
}
function checkb(flag) {
  document.nform.hq_days.disabled = document.nform.hq_days_nolimit.checked;
  document.nform.hreturn_profit.disabled = document.nform.hq_days_nolimit.checked;
  if (document.nform.hq_days_nolimit.checked == true) {
    i = document.nform.hperiod.options.length-1;
    if (document.nform.hperiod.selectedIndex == i) {
       document.nform.hperiod';
  echo '.selectedIndex = 0;
    }
    document.nform.hperiod.options[i] = null;
  } else {
    i = document.nform.hperiod.options.length;
    if (document.nform.hperiod.options[i-1].value != \'end\') {
      document.nform.hperiod.options[i] = new Option(\'After specifeid period\', \'end\');
    }
  }
  if (!flag)
  {
    CalculateProfit();
    InitCalendar();
  }
}
function checkc()
{
  document.nform.withdraw';
  echo '_principal_percent.disabled = (document.nform.withdraw_principal.checked) ? false : true;
  document.nform.withdraw_principal_duration.disabled = (document.nform.withdraw_principal.checked) ? false : true;
  document.nform.withdraw_principal_duration_max.disabled = (document.nform.withdraw_principal.checked) ? false : true;
}

function checkd()
{
  document.nform.compound_min_deposit.disabled = (d';
  echo 'ocument.nform.use_compound.checked) ? false : true;
  document.nform.compound_max_deposit.disabled = (document.nform.use_compound.checked) ? false : true;
  document.nform.compound_min_percent.disabled = (document.nform.use_compound.checked) ? false : true;
  document.nform.compound_max_percent.disabled = (document.nform.use_compound.checked) ? false : true;
  document.nform.compound_percents_type';
  echo '[0].disabled = (document.nform.use_compound.checked) ? false : true;
  document.nform.compound_percents_type[1].disabled = (document.nform.use_compound.checked) ? false : true;
  document.nform.compound_percents.disabled = (document.nform.use_compound.checked) ? false : true;
  checkd1();
}
function checkd1()
{
  if (document.nform.use_compound.checked)
  {
    if (document.nform.compound_percents';
  echo '_type[0].checked)
    {
      document.nform.compound_percents.disabled = true;
      document.nform.compound_min_percent.disabled = false;
      document.nform.compound_max_percent.disabled = false;
    }
    else
    {
      document.nform.compound_percents.disabled = false;
      document.nform.compound_min_percent.disabled = true;
      document.nform.compound_max_percent.disabled = true;
    ';
  echo '}
  }
}

CheckCompound();
checkb(1);
checkc();
checkd();checkd1();
</script>


<br>
<input type=hidden name=a value=\'edit_hyip\'>
<input type=hidden name=action value="edit_hyip">
<input type=submit value="Save Changes" class=sbmt size=15>
<input type="hidden" name="_token" value="'.csrf_token().'"></form>';
  echo '<s';
  echo 'cript language=javascript>
function checkrates(a, flag) {
  document.nform.elements["rate_min_amount["+a+"]"].disabled = !document.nform.elements["rate_amount_active["+a+"]"].checked;
  document.nform.elements["rate_amount_name["+a+"]"].disabled = !document.nform.elements["rate_amount_active["+a+"]"].checked;
  document.nform.elements["rate_max_amount["+a+"]"].disabled = !document.nform.elements["';
  echo 'rate_amount_active["+a+"]"].checked;
  document.nform.elements["rate_percent["+a+"]"].disabled = !document.nform.elements["rate_amount_active["+a+"]"].checked;

  if (!flag)
  {
    CalculateProfit();
    InitCalendar();
  }
}

for (i = 0; i';
  echo '<';
  echo $j - 1;
  echo '; i++) {
  checkrates(i, 1);
}
</script>
';
  include app_path('Hm').'/inc/admin/calendar.inc.php';
  echo '
<br>';
  echo start_info_table('100%');
  echo 'Edit your package here.<br>
<br>
Set a name, a package duration and rates. Select a payment period.<br>
<br>
Earnings only on mon-fri:<br>
Allow earnings only on working days.<br>
<br>
Allow depositing only after the user has deposited to the following package:<br>
Administrator can select a \'parent\' package. Then users should deposit to the
parent package before depositing to the current one.<br>
<br>
Com';
  echo 'pounding:<br>
Users can set a compounding percent while depositing. For example if one sets
the 40% compounding, then the system will add 40% of earnings to the deposit,
and 60% of earnings to the user\'s account.<br>
<br>
Compounding deposit amount limits:<br>
Here you can limit the deposit amount for which compounding is possible.<br>
<br>
Compounding percents limits:<br>
You can limit the compounding';
  echo ' percent here. The range or solid values are possible
to specify.<br>
<br>
Hold earnings at account:<br>
Use this feature if you like user can withdraw earning after several days only.<br><br>

Delay earnings:<br>
You can set initial delay. Then user\'s deposits start work after specified days only <br><br>

Example 1.<br>
Creating a package for unlimited period with 1.2% daily:<br>
Set the name, the rates,';
  echo ' check \'no limit\' in the duration field, select the \'daily\'
payment period, set the \'active\' status.<br>
Users will receive 1.2% daily for the unlimited period.<br>
<br>
Example 2.<br>
Creating a package for 30 days with 1.3% daily:<br>
Set the name, the rates, type 30 in the duration field, select the \'daily\' payment
period, set the \'active\' status and check \'return principal\'.<br>
Users will receive';
  echo ' 1.3% daily for 30 days and get their deposit back after 30
days. If they deposit $100, they will receive $100*0.013*30 + $100 (return principal)
= $139.<br>
<br>
Example 3.<br>
Creating a package for 1 year with 1.3% daily:<br>
Set the name, the rates, type 365 in the package duration field, select \'daily\'
payment period, set \'active\' status, do not check \'return principal\'<br>
Users will receive 1';
  echo '.3% daily for 1 year and will not receive his deposit
after 365 days. If they deposit $100, they will receive $100*0.013*365 = $474.5.<br>
<br>
Example 4.<br>
Creating a package for 1 month with rate 125%<br>
Set the name, the rates, type 31 in the package duration field, select \'after specified
period\' in the payment period field, set status \'active\' and do not check \'return
principal\'.<br>
Users w';
  echo 'ill receive 125% in a month. If one deposits $100, he will receive $100*1.25
= $125.<br>
<br>
Example 5.<br>
Creating a package for 1 month with 30% weekly rate:<br>
Set the name, the rates, type 31 in the package duration field, select \'weekly\'
payment period, set \'active\' status, do not check \'return principal\'.<br>
Users will receive 30% weekly. If one deposits $100, he will receive $100*0.30*4
=';
  echo ' $120.<br>
<br>

';
  echo end_info_table();
