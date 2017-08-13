<?php

/*
 * This file is part of the entimm/hm.
 *
 * (c) entimm <entimm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

$q = 'select * from hm2_types where status = \'on\'';
  if (!($sth = db_query($q))) {
  }

  $packages = [];
  while ($row1 = mysql_fetch_array($sth)) {
      array_push($packages, $row1);
  }

  echo '<s';
  echo 'cript language=javascript>
function checkform() {
  if (document.nform.hname.value==\'\') {
    alert("Please type HYIP name!");
    document.nform.hname.focus();
    return false;
  }

  return true;
}
</script>

<form method=post name=nform onsubmit="return checkform();">

<table cellspacing=1 cellpadding=2 border=0 width=100%>
<tr>
      <td colspan=2><b>Add a New Investment Package:</b></td>
</tr><tr>
   ';
  echo '   <td width=150><a href="javascript:alert(\'Enter your package name here.\')" class=hlp>Package
        Name</a></td>
 <td><input type=text name=hname class=inpts size=30 value="New Package"></td>
</tr><tr>
      <td><a href="javascript:alert(\'Specify your package duration here.\\nFor example 30 days, 365 days, or no limit.\')" class=hlp>Package
        Duration</a></td>
 <td>
	<input type=text name=hq_days class';
  echo '=inpts size=5 style="text-align:right" value="365"> in days
	(<input type=checkbox name=hq_days_nolimit value=1 onclick="checkb()"> no limit)
 </td>
</tr><tr>
 <td colspan=2>
<table cellspacing=0 cellpadding=2 border=0 width=100%><tr><td valign=top>
<table cellspacing=0 cellpadding=2 border=0 width=360>
<tr>
                  <td colspan=3><b>Specify the Rates:</b></td>
</tr><tr>
 <td align=center>#</td>
 <td align=';
  echo 'center>Name</td>
 <td align=center>Min Amount</td>
 <td align=center>Max Amount</td>
 <td align=center>Percent</td>
</tr><tr>
 <td align=center>1. <input type=checkbox name="rate_amount_active[0]" onclick="checkrates(0)" value=1 checked></td>
 <td align=center><input type=text name="rate_amount_name[0]" value="Plan 1" class=inpts size=10></td>
 <td align=center><input type=text name="rate_min_amount[0]" value="10"';
  echo ' class=inpts size=10 style="text-align:right"></td>
 <td align=center><input type=text name="rate_max_amount[0]" value="100" class=inpts size=10 style="text-align:right"></td>
 <td align=center><input type=text name="rate_percent[0]" value="3.2" class=inpts size=10 style="text-align:right"></td>
</tr><tr>
 <td align=center>2. <input type=checkbox name="rate_amount_active[1]" onclick="checkrates(1)" value=1 c';
  echo 'hecked></td>
 <td align=center><input type=text name="rate_amount_name[1]" value="Plan 2" class=inpts size=10></td>
 <td align=center><input type=text name="rate_min_amount[1]" value="101" class=inpts size=10 style="text-align:right"></td>
 <td align=center><input type=text name="rate_max_amount[1]" value="1000" class=inpts size=10 style="text-align:right"></td>
 <td align=center><input type=text name="rate_p';
  echo 'ercent[1]" value="3.3" class=inpts size=10 style="text-align:right"></td>
</tr><tr>
 <td align=center>3. <input type=checkbox name="rate_amount_active[2]" onclick="checkrates(2)" value=1 checked></td>
 <td align=center><input type=text name="rate_amount_name[2]" value="Plan 3" class=inpts size=10></td>
 <td align=center><input type=text name="rate_min_amount[2]" value="1001" class=inpts size=10 style="text-a';
  echo 'lign:right"></td>
 <td align=center><input type=text name="rate_max_amount[2]" value="5000" class=inpts size=10 style="text-align:right"></td>
 <td align=center><input type=text name="rate_percent[2]" value="3.4" class=inpts size=10 style="text-align:right"></td>
</tr><tr>
 <td align=center>4. <input type=checkbox name="rate_amount_active[3]" onclick="checkrates(3)" value=1></td>
 <td align=center><input type=t';
  echo 'ext name="rate_amount_name[3]" value="Plan 4" class=inpts size=10></td>
 <td align=center><input type=text name="rate_min_amount[3]" value="5001" class=inpts size=10 style="text-align:right"></td>
 <td align=center><input type=text name="rate_max_amount[3]" value="10000" class=inpts size=10 style="text-align:right"></td>
 <td align=center><input type=text name="rate_percent[3]" value="3.5" class=inpts size';
  echo '=10 style="text-align:right"></td>
</tr><tr>
 <td align=center>5. <input type=checkbox name="rate_amount_active[4]" onclick="checkrates(4)" value=1></td>
 <td align=center><input type=text name="rate_amount_name[4]" value="Plan 5" class=inpts size=10></td>
 <td align=center><input type=text name="rate_min_amount[4]" value="10001" class=inpts size=10 style="text-align:right"></td>
 <td align=center><input type=t';
  echo 'ext name="rate_max_amount[4]" value="" class=inpts size=10 style="text-align:right"></td>
 <td align=center><input type=text name="rate_percent[4]" value="3.6" class=inpts size=10 style="text-align:right"></td>
</tr></table>
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
 <td colspan=2><textarea cols=80 rows=10 name=plan_description class=inpts></textarea><br><br></td>
</tr><tr>
 <td><a href="javascript:alert(\'Specify here when a user will get earning from a deposit in this package.\')" class=hlp>Payment period:</a></td>
 <td>
	';
  echo '<s';
  echo 'elect name=hperiod class=inpts onchange="CheckCompound();CalculateProfit();InitCalendar();">
		<option value="d">Daily
		<option value="w">Weekly
		<option value="b-w">Bi-weekly
		<option value="m">Monthly
		<option value="2m">Every 2 months
		<option value="3m">Every 3 months
		<option value="6m">Every 6 months
		<option value="y">Yearly
		<option value="end">After the specified period</select>
 </td>
</t';
  echo 'r><tr>
 <td><a href="javascript:alert(\'Users will receive earnings if the package status is active.\')" class=hlp>Status</td>
 <td>
	';
  echo '<s';
  echo 'elect name=hstatus class=inpts>
		<option value=\'on\'>Active
		<option value=\'off\'>Inactive</select>
 </td>
</tr><tr>
 <td colspan=2><input type=checkbox name=hreturn_profit value=1 checked onclick="CalculateProfit();InitCalendar();"><a href="javascript:alert(\'You can return the principal to user account when the package is finished.\')" class=hlp>
        Return principal after the plan completion</td>
</tr>';
  echo '<tr>
 <td colspan=2><input type=checkbox name=use_compound value=1 checked onclick="checkd();CalculateProfit();InitCalendar();"><a href="javascript:alert(\'You can use the compounding for this package.\')" class=hlp>
        Use compounding</td>
</tr><tr>
 <td rowspan=2> &nbsp; Compounding deposit amount limits:</td>
 <td>min: <input type=input name=compound_min_deposit value="0" class=inpts size=6> max: <inpu';
  echo 't type=input name=compound_max_deposit value="0" class=inpts size=6></td>
</tr><tr>
 <td>';
  echo '<s';
  echo 'mall>set 0 as max to skip limitation</small></td>
</tr><tr>
      <td colspan=2> &nbsp; Compounding percent limits:</td>
</tr><tr>
 <td> &nbsp; <input type=radio name=compound_percents_type value=0 checked onclick="checkd1()">
        Compounding percent:</td>
 <td>min: <input type=input name=compound_min_percent value="0" class=inpts size=6> max: <input type=input name=compound_max_percent value="100" class=in';
  echo 'pts size=6></td>
</tr><tr>
 <td> &nbsp; <input type=radio name=compound_percents_type value=1 onclick="checkd1()">
        Compounding percent solid values:<br> &nbsp;  &nbsp;  &nbsp;  &nbsp;';
  echo '<s';
  echo 'mall>comma separated (ex: 0,30,50,70,100)</small></td>
 <td><input type=input name=compound_percents value="0,30,50,70,100" class=inpts></td>
</tr><tr>
 <td colspan=2><input type=checkbox name=withdraw_principal value=1 onclick="checkc()"><a href="javascript:alert(\'You can allow users to return principal to user account and withdraw it. You can define a fee for this transaction and a minimal deposit duratio';
  echo 'n.\')" class=hlp>
        Allow principal withdrawal.</td>
</tr><tr>
      <td> &nbsp; The principal withdrawal fee:</td>
 <td><input type=input name=withdraw_principal_percent value="10.00" class=inpts> %</td>
</tr><tr>
      <td> &nbsp; Enter the minimal deposit withdrawal duration:</td>
 <td><input type=input name=withdraw_principal_duration value="20" class=inpts> days</td>
</tr><tr>
      <td> &nbsp; Enter the ';
  echo 'maximal deposit withdrawal duration:</td>
 <td><input type=input name=withdraw_principal_duration_max value="0" class=inpts> days<br>';
  echo '<s';
  echo 'mall>set 0 to skip limitation</small></td>
</tr><tr>
 <td colspan=2><input type=checkbox name=\'work_week\' value=1 ';
  echo $row['work_week'] == 1 ? 'checked' : '';
  echo ' onclick="CalculateProfit();InitCalendar();"><a href="javascript:alert(\'Earnings will accumulate on user accounts only  on Mon-Fri. Available for daily payment plans.\')" class=hlp>
        Earnings only on mon-fri</td>
</tr>';
  if (0 < count($packages)) {
      echo '<tr>
 <td colspan=2><input type=checkbox name=parentch value=1 ';
      echo $row['parent'] == 0 ? '' : 'checked';
      echo '>
        <a href="javascript:alert(\'Administrator can select a \\\'parent\\\' package. Then users should deposit to parent package before depositing to this one.\')" class=hlp>Allow
        depositing only after the user has deposited to the following package:</a>
        <br> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;';
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
	Hold earnings at account for <input type=text name=hold value=\'0\' class=inpts size=5> days after payout (set 0 for disable this feature)
 </td>
</tr>
<tr>
 <td colspan=2>
	Delay earning for <input type=text name=delay value=\'0\' class=inpts size=5> days since deposit (set 0 for disable this feature)
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


checkb(1);
checkc();
checkd();checkd1();
</script>

<br>
<input type=hidden name=a value=\'add_hyip\'>
<input type=hidden name=action value="add_hyip">
<input type=submit value="Add Package" class=sbmt size=15>
</form>';
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
checkrates(0,1);
checkrates(1,1);
checkrates(2,1);
checkrates(3,1);
checkrates(4,1);
</script>
';
  include app_path('Hm').'/inc/admin/calendar.inc.php';
  echo '
<br>';
  echo start_info_table('100%');
  echo 'Create your package.<br><br>
Set a name, a package duration, and rates. Select a payment period.<br>
<br>

Compounding:<br>
Users can set a compounding percent while depositing. For example if one sets
the 40% compounding, then the system will add 40% of earnings to the deposit,
and 60% of earnings to the user\'s account.<br><br>
Compounding deposit amount limits:<br>
Here you can limit the deposit amoun';
  echo 't for which compounding is possible.<br>
<br>
Compounding percents limits:<br>
You can limit the compounding percent here. The range or solid values are possible
to specify.<br><br>

Example 1.<br>
Creating a package for unlimited period with 1.2% daily:<br>
Set the name, the rates, check \'no limit\' in the duration field, select the \'daily\'
payment period, set the \'active\' status.<br>
Users will receive';
  echo ' 1.2% daily for the unlimited period.<br>
<br>
Example 2.<br>
Creating a package for 30 days with 1.3% daily:<br>
Set the name, the rates, type 30 in the duration field, select the \'daily\' payment
period, set the \'active\' status and check \'return principal\'.<br>
Users will receive 1.3% daily for 30 days and get their deposit back after 30
days. If they deposit $100, they will receive $100*0.013*30 + ';
  echo '$100 (return principal)
= $139.<br>
<br>
Example 3.<br>
Creating a package for 1 year with 1.3% daily:<br>
Set the name, the rates, type 365 in the package duration field, select \'daily\'
payment period, set \'active\' status, do not check \'return principal\'<br>
Users will receive 1.3% daily for 1 year and will not receive his deposit
after 365 days. If they deposit $100, they will receive $100*0.013*3';
  echo '65 = $474.5.<br>
<br>
Example 4.<br>
Creating a package for 1 month with rate 125%<br>
Set the name, the rates, type 31 in the package duration field, select \'after specified
period\' in the payment period field, set status \'active\' and do not check \'return
principal\'.<br>
Users will receive 125% in a month. If one deposits $100, he will receive $100*1.25
= $125.<br>
<br>
Example 5.<br>
Creating a packa';
  echo 'ge for 1 month with 30% weekly rate:<br>
Set the name, the rates, type 31 in the package duration field, select \'weekly\'
payment period, set \'active\' status, do not check \'return principal\'.<br>
Users will receive 30% weekly. If one deposits $100, he will receive $100*0.30*4
= $120.<br>
<br>

<br>
Do the following if you need to create more than 5 plans:<br>
Fill all 5 plans, click \'save\' button, find';
  echo ' this package in a package list and
edit it. You will be able add the additional plans. (You can create unlimited
number of plans in this way).';
  echo end_info_table();
